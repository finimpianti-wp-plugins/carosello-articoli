/* Carosello Articoli */
(function () {
	'use strict';

	var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	class Carousel {
		constructor(root) {
			this.root = root;
			this.cfg = JSON.parse(root.getAttribute('data-config') || '{}');
			this.viewport = root.querySelector('.cda__viewport');
			this.track = root.querySelector('.cda__track');
			this.dotsWrap = root.querySelector('.cda__dots');
			this.prevBtn = root.querySelector('.cda__arrow--prev');
			this.nextBtn = root.querySelector('.cda__arrow--next');
			this.slides = Array.from(this.track.children);
			this.n = this.slides.length;
			this.current = 0; // indice reale (0..n-1) della prima card visibile
			this.paused = {};
			this.width = 0;

			this.bindEvents();
			this.setup();
		}

		perView() {
			var w = window.innerWidth, c = this.cfg;
			var pv = w <= c.bpMobile ? c.mobile : (w <= c.bpTablet ? c.tablet : c.desktop);
			return Math.max(1, pv || 1);
		}

		/* Costruisce (o ricostruisce dopo un resize) cloni, misure e pallini. */
		setup() {
			var c = this.cfg;

			this.track.querySelectorAll('.cda__slide--clone').forEach(function (el) { el.remove(); });

			this.pvLayout = this.perView();
			this.pv = Math.min(this.pvLayout, this.n);
			this.canMove = this.n > this.pv;
			this.loop = !!c.loop && this.canMove;
			this.cc = this.loop ? this.pv + 1 : 0; // cloni per lato

			if (this.loop) {
				var before = this.slides.slice(this.n - this.cc).map(this.makeClone);
				var after = this.slides.slice(0, this.cc).map(this.makeClone);
				this.track.prepend.apply(this.track, before);
				this.track.append.apply(this.track, after);
			}
			this.all = Array.from(this.track.children);

			var W = this.viewport.clientWidth;
			this.width = W;
			this.peek = Math.round(Math.min(c.peek || 0, W * 0.12));
			this.gap = c.gap || 0;
			this.cardW = (W - 2 * this.peek - (this.pvLayout - 1) * this.gap) / this.pvLayout;
			this.step = this.cardW + this.gap;
			this.maxIndex = this.n - this.pv;
			this.root.style.setProperty('--cda-peek-px', this.peek + 'px');
			this.all.forEach(function (s) { s.style.width = this.cardW + 'px'; }, this);

			this.root.classList.toggle('is-static', !this.canMove);
			this.index = this.loop ? this.cc + this.current : Math.min(this.current, this.maxIndex);

			this.buildDots();
			this.render(false);
			this.reflow();
			this.updateUI();
			this.restart();
		}

		makeClone(el) {
			var clone = el.cloneNode(true);
			clone.classList.add('cda__slide--clone');
			clone.setAttribute('aria-hidden', 'true');
			clone.querySelectorAll('a, button').forEach(function (a) { a.tabIndex = -1; });
			return clone;
		}

		reflow() {
			void this.track.offsetWidth;
		}

		realIndex() {
			return this.loop ? (((this.index - this.cc) % this.n) + this.n) % this.n : this.index;
		}

		render(animate, extra) {
			var x = this.canMove ? this.peek - this.index * this.step + (extra || 0) : 0;
			this.track.style.transition = animate ? 'transform ' + this.cfg.speed + 'ms cubic-bezier(.25,.8,.25,1)' : 'none';
			this.track.style.transform = this.canMove ? 'translate3d(' + x + 'px,0,0)' : '';
			for (var i = 0; i < this.all.length; i++) {
				this.all[i].classList.toggle('is-visible', !this.canMove || (i >= this.index && i < this.index + this.pv));
			}
		}

		goTo(i, animate) {
			if (!this.canMove) return;
			if (animate === undefined) animate = true;
			this.settle();
			this.index = this.loop
				? Math.max(0, Math.min(this.all.length - this.pv, i))
				: Math.max(0, Math.min(this.maxIndex, i));
			this.current = this.realIndex();
			this.render(animate);
			this.updateUI();
			if (this.loop) {
				if (animate) {
					var self = this;
					this.settleTimer = setTimeout(function () { self.settle(); }, this.cfg.speed + 30);
				} else {
					this.normalize();
				}
			}
			this.restart();
		}

		/* A fine animazione, se siamo su un clone, salta senza animazione alla card reale equivalente. */
		settle() {
			if (this.settleTimer) {
				clearTimeout(this.settleTimer);
				this.settleTimer = null;
			}
			this.normalize();
		}

		normalize() {
			if (!this.loop) return;
			var i = this.index;
			if (i >= this.cc + this.n) i -= this.n;
			else if (i < this.cc) i += this.n;
			if (i !== this.index) {
				this.index = i;
				this.render(false);
				this.reflow();
			}
		}

		next(fromAutoplay) {
			if (!this.loop && this.index >= this.maxIndex) {
				if (fromAutoplay) this.goTo(0);
				return;
			}
			this.goTo(this.index + 1);
		}

		prev() {
			if (!this.loop && this.index <= 0) return;
			this.goTo(this.index - 1);
		}

		buildDots() {
			if (!this.dotsWrap) return;
			var self = this;
			var count = this.canMove ? (this.loop ? this.n : this.maxIndex + 1) : 0;
			this.dotsWrap.innerHTML = '';
			this.dotsWrap.hidden = count <= 1;
			this.dots = [];
			for (var k = 0; k < count; k++) {
				var b = document.createElement('button');
				b.type = 'button';
				b.className = 'cda__dot';
				b.setAttribute('aria-label', (self.cfg.goTo || 'Vai all\'articolo %d').replace('%d', k + 1));
				b.addEventListener('click', (function (k) {
					return function () { self.goTo(self.loop ? self.cc + k : k); };
				})(k));
				this.dotsWrap.appendChild(b);
				this.dots.push(b);
			}
		}

		updateUI() {
			var active = this.realIndex();
			(this.dots || []).forEach(function (d, k) {
				d.classList.toggle('is-active', k === active);
				if (k === active) d.setAttribute('aria-current', 'true');
				else d.removeAttribute('aria-current');
			});
			if (this.prevBtn) {
				this.prevBtn.hidden = this.nextBtn.hidden = !this.canMove;
				this.prevBtn.disabled = !this.loop && this.index <= 0;
				this.nextBtn.disabled = !this.loop && this.index >= this.maxIndex;
			}
		}

		/* --- Scorrimento automatico --- */

		stop() {
			clearTimeout(this.timer);
			this.timer = null;
		}

		restart() {
			this.stop();
			if (!this.cfg.autoplay || !this.canMove || reduceMotion) return;
			for (var k in this.paused) {
				if (this.paused[k]) return;
			}
			var self = this;
			this.timer = setTimeout(function () { self.next(true); }, this.cfg.interval);
		}

		pause(reason, on) {
			this.paused[reason] = on;
			if (on) this.stop();
			else this.restart();
		}

		/* --- Eventi --- */

		bindEvents() {
			var self = this, c = this.cfg, root = this.root;

			if (this.prevBtn) this.prevBtn.addEventListener('click', function () { self.prev(); });
			if (this.nextBtn) this.nextBtn.addEventListener('click', function () { self.next(); });

			root.addEventListener('keydown', function (e) {
				if (e.key === 'ArrowLeft') { self.prev(); e.preventDefault(); }
				if (e.key === 'ArrowRight') { self.next(); e.preventDefault(); }
			});

			if (c.pauseHover) {
				root.addEventListener('pointerenter', function (e) { if (e.pointerType === 'mouse') self.pause('hover', true); });
				root.addEventListener('pointerleave', function (e) { if (e.pointerType === 'mouse') self.pause('hover', false); });
			}

			// Pausa mentre si naviga con la tastiera dentro il carosello.
			root.addEventListener('focusin', function (e) {
				var t = e.target;
				if (t.matches && t.matches(':focus-visible')) self.pause('focus', true);

				// Se il focus finisce su una card fuori vista, portala in vista.
				var slide = t.closest && t.closest('.cda__slide');
				if (slide && self.canMove && !slide.classList.contains('is-visible')) {
					var idx = self.slides.indexOf(slide);
					if (idx > -1) self.goTo(self.loop ? self.cc + idx : idx);
				}
			});
			root.addEventListener('focusout', function (e) {
				if (!root.contains(e.relatedTarget)) self.pause('focus', false);
			});

			// Il browser può scorrere il viewport per mostrare un elemento con focus: lo annulliamo.
			this.viewport.addEventListener('scroll', function () { self.viewport.scrollLeft = 0; });

			document.addEventListener('visibilitychange', function () { self.pause('hidden', document.hidden); });

			if ('IntersectionObserver' in window) {
				new IntersectionObserver(function (entries) {
					self.pause('offscreen', !entries[0].isIntersecting);
				}).observe(root);

				// Quando il carosello si avvicina, carica tutte le immagini (anche quelle fuori vista).
				var imgObserver = new IntersectionObserver(function (entries) {
					if (!entries[0].isIntersecting) return;
					root.querySelectorAll('img[loading="lazy"]').forEach(function (img) { img.loading = 'eager'; });
					imgObserver.disconnect();
				}, { rootMargin: '400px' });
				imgObserver.observe(root);
			}

			var resizeTimer;
			var onResize = function () {
				clearTimeout(resizeTimer);
				resizeTimer = setTimeout(function () {
					if (self.viewport.clientWidth !== self.width || self.perView() !== self.pvLayout) {
						self.settle();
						self.setup();
					}
				}, 150);
			};
			if ('ResizeObserver' in window) new ResizeObserver(onResize).observe(root);
			window.addEventListener('resize', onResize);

			this.track.addEventListener('dragstart', function (e) { e.preventDefault(); });

			if (c.drag) this.bindDrag();
		}

		bindDrag() {
			var self = this, vp = this.viewport;
			var start = null, dx = 0, dragging = false, suppressClick = false;

			vp.addEventListener('pointerdown', function (e) {
				if (!self.canMove || e.button > 0) return;
				self.settle();
				start = { x: e.clientX, y: e.clientY, id: e.pointerId };
				dx = 0;
				dragging = false;
			});

			vp.addEventListener('pointermove', function (e) {
				if (!start || e.pointerId !== start.id) return;
				dx = e.clientX - start.x;
				var dy = e.clientY - start.y;
				if (!dragging) {
					if (Math.abs(dx) < 6 && Math.abs(dy) < 6) return;
					if (Math.abs(dy) > Math.abs(dx)) { start = null; return; } // scroll verticale della pagina
					dragging = true;
					try { vp.setPointerCapture(e.pointerId); } catch (err) { /* ignora */ }
					self.root.classList.add('is-dragging');
					self.pause('drag', true);
				}
				var off = dx;
				if (self.loop) {
					var lim = self.pv * self.step;
					off = Math.max(-lim, Math.min(lim, dx));
				} else if ((self.index <= 0 && dx > 0) || (self.index >= self.maxIndex && dx < 0)) {
					off = dx / 3; // resistenza ai bordi
				}
				self.render(false, off);
			});

			var end = function () {
				if (!start) return;
				start = null;
				if (!dragging) return;
				dragging = false;
				suppressClick = true;
				setTimeout(function () { suppressClick = false; }, 50);
				self.root.classList.remove('is-dragging');

				var steps = Math.round(-dx / self.step);
				if (steps === 0 && Math.abs(dx) > Math.min(50, self.step * 0.15)) steps = dx < 0 ? 1 : -1;
				steps = Math.max(-self.pv, Math.min(self.pv, steps));
				self.paused.drag = false;
				self.goTo(self.index + steps);
			};
			vp.addEventListener('pointerup', end);
			vp.addEventListener('pointercancel', end);

			// Dopo un trascinamento non aprire il link sotto il puntatore.
			vp.addEventListener('click', function (e) {
				if (suppressClick) {
					e.preventDefault();
					e.stopPropagation();
					suppressClick = false;
				}
			}, true);
		}
	}

	function init(scope) {
		(scope || document).querySelectorAll('.cda:not([data-cda-ready])').forEach(function (el) {
			el.setAttribute('data-cda-ready', '');
			try {
				new Carousel(el);
			} catch (err) {
				if (window.console) console.error('Carosello Articoli:', err);
			}
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () { init(); });
	} else {
		init();
	}

	// Elementor (anche nell'editor): inizializza i caroselli nei widget renderizzati dinamicamente.
	function hookElementor() {
		if (window.elementorFrontend && window.elementorFrontend.hooks) {
			window.elementorFrontend.hooks.addAction('frontend/element_ready/global', function ($scope) {
				init($scope && $scope[0]);
			});
		}
	}
	if (window.jQuery) {
		window.jQuery(window).on('elementor/frontend/init', hookElementor);
	}
	hookElementor();
})();
