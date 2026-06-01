(function () {
    "use strict";

    var exampleModal = document.getElementById('formmodal');
    if (exampleModal) {
        exampleModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var recipient = button ? button.getAttribute('data-bs-whatever') : '';
            var modalTitle = exampleModal.querySelector('.modal-title');
            var modalBodyInput = exampleModal.querySelector('.modal-body input');
            if (modalTitle) {
                modalTitle.textContent = 'New message to ' + recipient;
            }
            if (modalBodyInput) {
                modalBodyInput.value = recipient;
            }
        });
    }

    // Animated modals 
    var animatedModal = document.getElementById("modaldemo8");
    if (animatedModal) {
        /* showing modal effects */
        document.querySelectorAll(".modal-effect").forEach(trigger => {
            trigger.addEventListener('click', function (event) {
                event.preventDefault();
                var effect = this.getAttribute('data-bs-effect');
                if (effect) {
                    animatedModal.classList.add(effect);
                }
            });
        });
        /* hide modal effects */
        animatedModal.addEventListener('hidden.bs.modal', function () {
            var removeClass = this.classList.value.match(/(^|\s)effect-\S+/g);
            if (removeClass && removeClass[0]) {
                this.classList.remove(removeClass[0].trim());
            }
        });
    }
    // Animated modals 

})();
