{{-- Guided Tour Component --}}
@auth
@if(isset($instance))
<style>
.b360-tour-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    z-index: 99998;
    transition: opacity 0.3s ease;
}

.b360-tour-highlight {
    position: absolute;
    z-index: 99999;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.7), 0 0 0 9999px rgba(0, 0, 0, 0.55);
    border-radius: 6px;
    transition: all 0.4s ease;
    pointer-events: none;
}

.b360-tour-highlight-pulse {
    animation: b360TourPulse 2s infinite;
}

@keyframes b360TourPulse {
    0%, 100% { box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.7), 0 0 0 9999px rgba(0, 0, 0, 0.55); }
    50% { box-shadow: 0 0 0 8px rgba(59, 130, 246, 0.4), 0 0 0 9999px rgba(0, 0, 0, 0.55); }
}

.b360-tour-tooltip {
    position: absolute;
    z-index: 100000;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    padding: 24px;
    max-width: 400px;
    min-width: 300px;
    font-family: inherit;
    transition: all 0.3s ease;
}

.b360-tour-tooltip-center {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    max-width: 480px;
}

.b360-tour-tooltip-title {
    font-size: 16px;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0 0 10px 0;
    line-height: 1.3;
}

.b360-tour-tooltip-content {
    font-size: 14px;
    color: #4a4a6a;
    line-height: 1.6;
    margin: 0 0 20px 0;
}

.b360-tour-progress {
    display: flex;
    align-items: center;
    gap: 4px;
    margin-bottom: 16px;
}

.b360-tour-progress-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #e0e0e0;
    transition: background 0.3s;
}

.b360-tour-progress-dot-active {
    background: #3b82f6;
}

.b360-tour-progress-dot-done {
    background: #10b981;
}

.b360-tour-progress-text {
    font-size: 12px;
    color: #999;
    margin-left: 8px;
}

.b360-tour-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.b360-tour-btn {
    padding: 8px 18px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
}

.b360-tour-btn-skip {
    background: transparent;
    color: #999;
    padding: 8px 12px;
}

.b360-tour-btn-skip:hover {
    color: #666;
}

.b360-tour-btn-prev {
    background: #f1f5f9;
    color: #475569;
}

.b360-tour-btn-prev:hover {
    background: #e2e8f0;
}

.b360-tour-btn-next {
    background: #3b82f6;
    color: #ffffff;
}

.b360-tour-btn-next:hover {
    background: #2563eb;
}

.b360-tour-btn-finish {
    background: #10b981;
    color: #ffffff;
}

.b360-tour-btn-finish:hover {
    background: #059669;
}

.b360-tour-arrow {
    position: absolute;
    width: 12px;
    height: 12px;
    background: #ffffff;
    transform: rotate(45deg);
}

/* Help button dropdown */
.b360-help-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 8px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    min-width: 260px;
    z-index: 1050;
    display: none;
    overflow: hidden;
}

.b360-help-dropdown.show {
    display: block;
}

.b360-help-dropdown-header {
    padding: 14px 16px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    font-weight: 600;
    font-size: 14px;
    color: #334155;
}

.b360-help-dropdown-item {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    color: #475569;
    text-decoration: none;
    font-size: 13px;
    transition: background 0.15s;
    cursor: pointer;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
}

.b360-help-dropdown-item:hover {
    background: #f1f5f9;
    color: #1e293b;
}

.b360-help-dropdown-item i {
    margin-right: 10px;
    font-size: 16px;
    width: 20px;
    text-align: center;
}

.b360-help-dropdown-divider {
    height: 1px;
    background: #e2e8f0;
    margin: 0;
}

.b360-help-tour-list {
    max-height: 200px;
    overflow-y: auto;
}
</style>

<script>
(function() {
    'use strict';

    var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    var instanceSlug = '{{ $instance->slug }}';
    var baseUrl = '/i/' + instanceSlug + '/tours';

    // B360 Tour Engine
    var B360Tour = {
        currentTour: null,
        currentStep: 0,
        steps: [],
        overlay: null,
        highlight: null,
        tooltip: null,

        // Start a tour by ID
        start: function(tourId) {
            var self = this;
            fetch(baseUrl + '/' + tourId + '/steps', {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.steps || data.steps.length === 0) return;
                self.currentTour = tourId;
                self.steps = data.steps;
                self.currentStep = 0;
                self.createOverlay();
                self.showStep();
            })
            .catch(function() {});
        },

        // Create the overlay element
        createOverlay: function() {
            this.cleanup();

            this.overlay = document.createElement('div');
            this.overlay.className = 'b360-tour-overlay';
            document.body.appendChild(this.overlay);

            this.highlight = document.createElement('div');
            this.highlight.className = 'b360-tour-highlight b360-tour-highlight-pulse';
            document.body.appendChild(this.highlight);

            this.tooltip = document.createElement('div');
            this.tooltip.className = 'b360-tour-tooltip';
            document.body.appendChild(this.tooltip);
        },

        // Show current step
        showStep: function() {
            var step = this.steps[this.currentStep];
            if (!step) return;

            var targetEl = null;
            if (step.target) {
                // Try each selector separated by comma
                var selectors = step.target.split(',');
                for (var i = 0; i < selectors.length; i++) {
                    try {
                        targetEl = document.querySelector(selectors[i].trim());
                        if (targetEl) break;
                    } catch(e) {}
                }
            }

            // Position highlight
            if (targetEl) {
                var rect = targetEl.getBoundingClientRect();
                var pad = 6;
                this.highlight.style.display = 'block';
                this.highlight.style.top = (rect.top + window.scrollY - pad) + 'px';
                this.highlight.style.left = (rect.left + window.scrollX - pad) + 'px';
                this.highlight.style.width = (rect.width + pad * 2) + 'px';
                this.highlight.style.height = (rect.height + pad * 2) + 'px';
                this.overlay.style.display = 'none';

                // Scroll into view if needed
                targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                this.highlight.style.display = 'none';
                this.overlay.style.display = 'block';
            }

            // Build tooltip content
            this.tooltip.textContent = '';

            // Progress dots
            var progressDiv = document.createElement('div');
            progressDiv.className = 'b360-tour-progress';
            for (var d = 0; d < this.steps.length; d++) {
                var dot = document.createElement('span');
                dot.className = 'b360-tour-progress-dot';
                if (d < this.currentStep) dot.className += ' b360-tour-progress-dot-done';
                if (d === this.currentStep) dot.className += ' b360-tour-progress-dot-active';
                progressDiv.appendChild(dot);
            }
            var progText = document.createElement('span');
            progText.className = 'b360-tour-progress-text';
            progText.textContent = (this.currentStep + 1) + ' / ' + this.steps.length;
            progressDiv.appendChild(progText);
            this.tooltip.appendChild(progressDiv);

            // Title
            var titleEl = document.createElement('div');
            titleEl.className = 'b360-tour-tooltip-title';
            titleEl.textContent = step.title || '';
            this.tooltip.appendChild(titleEl);

            // Content
            var contentEl = document.createElement('div');
            contentEl.className = 'b360-tour-tooltip-content';
            // Safe HTML rendering for content with <strong> etc.
            var tempDiv = document.createElement('div');
            tempDiv.textContent = step.content || '';
            // Replace known safe tags
            var safeContent = tempDiv.textContent;
            contentEl.textContent = safeContent;
            this.tooltip.appendChild(contentEl);

            // Actions
            var actionsDiv = document.createElement('div');
            actionsDiv.className = 'b360-tour-actions';

            var self = this;

            // Skip button
            var skipBtn = document.createElement('button');
            skipBtn.className = 'b360-tour-btn b360-tour-btn-skip';
            skipBtn.textContent = 'Passer';
            skipBtn.addEventListener('click', function() { self.skip(); });
            actionsDiv.appendChild(skipBtn);

            var rightBtns = document.createElement('div');
            rightBtns.style.cssText = 'display:flex;gap:8px;';

            // Prev button
            if (this.currentStep > 0) {
                var prevBtn = document.createElement('button');
                prevBtn.className = 'b360-tour-btn b360-tour-btn-prev';
                prevBtn.textContent = 'Precedent';
                prevBtn.addEventListener('click', function() { self.prev(); });
                rightBtns.appendChild(prevBtn);
            }

            // Next / Finish button
            if (this.currentStep < this.steps.length - 1) {
                var nextBtn = document.createElement('button');
                nextBtn.className = 'b360-tour-btn b360-tour-btn-next';
                nextBtn.textContent = 'Suivant';
                nextBtn.addEventListener('click', function() { self.next(); });
                rightBtns.appendChild(nextBtn);
            } else {
                var finishBtn = document.createElement('button');
                finishBtn.className = 'b360-tour-btn b360-tour-btn-finish';
                finishBtn.textContent = 'Terminer';
                finishBtn.addEventListener('click', function() { self.finish(); });
                rightBtns.appendChild(finishBtn);
            }

            actionsDiv.appendChild(rightBtns);
            this.tooltip.appendChild(actionsDiv);

            // Position tooltip
            this.positionTooltip(targetEl, step.position || 'center');
        },

        // Position the tooltip relative to target
        positionTooltip: function(targetEl, position) {
            // Remove center class first
            this.tooltip.classList.remove('b360-tour-tooltip-center');

            if (!targetEl || position === 'center') {
                this.tooltip.classList.add('b360-tour-tooltip-center');
                this.tooltip.style.top = '';
                this.tooltip.style.left = '';
                return;
            }

            var rect = targetEl.getBoundingClientRect();
            var tooltipRect = this.tooltip.getBoundingClientRect();
            var gap = 16;
            var top, left;

            switch (position) {
                case 'bottom':
                    top = rect.bottom + window.scrollY + gap;
                    left = rect.left + window.scrollX + (rect.width / 2) - (tooltipRect.width / 2);
                    break;
                case 'top':
                    top = rect.top + window.scrollY - tooltipRect.height - gap;
                    left = rect.left + window.scrollX + (rect.width / 2) - (tooltipRect.width / 2);
                    break;
                case 'left':
                    top = rect.top + window.scrollY + (rect.height / 2) - (tooltipRect.height / 2);
                    left = rect.left + window.scrollX - tooltipRect.width - gap;
                    break;
                case 'right':
                    top = rect.top + window.scrollY + (rect.height / 2) - (tooltipRect.height / 2);
                    left = rect.right + window.scrollX + gap;
                    break;
                default:
                    this.tooltip.classList.add('b360-tour-tooltip-center');
                    return;
            }

            // Keep in viewport
            var vw = window.innerWidth;
            var vh = window.innerHeight;
            if (left < 10) left = 10;
            if (left + tooltipRect.width > vw - 10) left = vw - tooltipRect.width - 10;
            if (top < 10) top = 10;

            this.tooltip.style.top = top + 'px';
            this.tooltip.style.left = left + 'px';
        },

        next: function() {
            if (this.currentStep < this.steps.length - 1) {
                this.currentStep++;
                this.showStep();
            }
        },

        prev: function() {
            if (this.currentStep > 0) {
                this.currentStep--;
                this.showStep();
            }
        },

        skip: function() {
            this.markComplete();
            this.cleanup();
        },

        finish: function() {
            this.markComplete();
            this.cleanup();
        },

        markComplete: function() {
            if (!this.currentTour) return;
            fetch(baseUrl + '/' + this.currentTour + '/complete', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            }).catch(function() {});
        },

        cleanup: function() {
            if (this.overlay && this.overlay.parentNode) this.overlay.parentNode.removeChild(this.overlay);
            if (this.highlight && this.highlight.parentNode) this.highlight.parentNode.removeChild(this.highlight);
            if (this.tooltip && this.tooltip.parentNode) this.tooltip.parentNode.removeChild(this.tooltip);
            this.overlay = null;
            this.highlight = null;
            this.tooltip = null;
            this.currentTour = null;
            this.steps = [];
            this.currentStep = 0;
        }
    };

    // Expose globally
    window.B360Tour = B360Tour;

    // Help dropdown logic
    function initHelpButton() {
        var helpBtn = document.getElementById('b360-help-btn');
        var helpDropdown = document.getElementById('b360-help-dropdown');
        if (!helpBtn || !helpDropdown) return;

        helpBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            helpDropdown.classList.toggle('show');

            // Load available tours if dropdown just opened
            if (helpDropdown.classList.contains('show')) {
                loadTourList();
            }
        });

        document.addEventListener('click', function(e) {
            if (!helpDropdown.contains(e.target) && e.target !== helpBtn) {
                helpDropdown.classList.remove('show');
            }
        });
    }

    function loadTourList() {
        var container = document.getElementById('b360-tour-list');
        if (!container) return;

        container.textContent = '';
        var loading = document.createElement('div');
        loading.style.cssText = 'padding:12px 16px;color:#999;font-size:13px;';
        loading.textContent = 'Chargement...';
        container.appendChild(loading);

        fetch(baseUrl + '/available', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            container.textContent = '';
            if (!data.tours || data.tours.length === 0) {
                var empty = document.createElement('div');
                empty.style.cssText = 'padding:12px 16px;color:#999;font-size:13px;';
                empty.textContent = 'Aucune visite disponible';
                container.appendChild(empty);
                return;
            }

            data.tours.forEach(function(tour) {
                var item = document.createElement('button');
                item.className = 'b360-help-dropdown-item';
                item.type = 'button';

                var icon = document.createElement('i');
                icon.className = tour.completed ? 'ti ti-circle-check' : 'ti ti-player-play';
                icon.style.color = tour.completed ? '#10b981' : '#3b82f6';
                item.appendChild(icon);

                var textSpan = document.createElement('span');
                textSpan.style.cssText = 'flex:1;';
                var titleNode = document.createTextNode(tour.title);
                textSpan.appendChild(titleNode);
                if (tour.completed) {
                    var badge = document.createElement('span');
                    badge.style.cssText = 'display:inline-block;margin-left:6px;font-size:10px;color:#10b981;';
                    badge.textContent = '(terminee)';
                    textSpan.appendChild(badge);
                }
                item.appendChild(textSpan);

                item.addEventListener('click', function() {
                    document.getElementById('b360-help-dropdown').classList.remove('show');
                    B360Tour.start(tour.id);
                });

                container.appendChild(item);
            });

            // Reset tours link
            var divider = document.createElement('div');
            divider.className = 'b360-help-dropdown-divider';
            container.appendChild(divider);

            var resetBtn = document.createElement('button');
            resetBtn.className = 'b360-help-dropdown-item';
            resetBtn.type = 'button';
            resetBtn.style.color = '#ef4444';

            var resetIcon = document.createElement('i');
            resetIcon.className = 'ti ti-refresh';
            resetBtn.appendChild(resetIcon);

            var resetText = document.createTextNode('Reinitialiser les visites');
            resetBtn.appendChild(resetText);

            resetBtn.addEventListener('click', function() {
                fetch(baseUrl + '/reset', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
                }).then(function() {
                    loadTourList();
                });
            });

            container.appendChild(resetBtn);
        })
        .catch(function() {
            container.textContent = '';
            var err = document.createElement('div');
            err.style.cssText = 'padding:12px 16px;color:#ef4444;font-size:13px;';
            err.textContent = 'Erreur de chargement';
            container.appendChild(err);
        });
    }

    // Auto-start welcome tour for new users
    function autoStartWelcome() {
        fetch(baseUrl + '/available', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.tours) return;
            var welcome = data.tours.find(function(t) { return t.id === 'welcome' && !t.completed; });
            if (welcome) {
                // Small delay so the page loads fully
                setTimeout(function() { B360Tour.start('welcome'); }, 1000);
            }
        })
        .catch(function() {});
    }

    // Init on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initHelpButton();
            autoStartWelcome();
        });
    } else {
        initHelpButton();
        autoStartWelcome();
    }
})();
</script>
@endif
@endauth
