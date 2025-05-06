        </div>
    </div>
    <script>
        // Common JavaScript functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Flash messages auto-hide
            const flashMessages = document.querySelectorAll('.flash-message');
            flashMessages.forEach(message => {
                setTimeout(() => {
                    message.classList.add('opacity-0');
                    setTimeout(() => {
                        message.remove();
                    }, 300);
                }, 5000);
            });
            
            // Confirm delete actions
            const deleteButtons = document.querySelectorAll('.delete-confirm');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (!confirm('¿Está seguro de que desea eliminar este elemento? Esta acción no se puede deshacer.')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>
