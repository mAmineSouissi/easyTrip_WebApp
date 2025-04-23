document.addEventListener('DOMContentLoaded', function () {
    const createForm = document.getElementById('create-offer-form');
    const editForm = document.getElementById('edit-offer-form');

    // Validation pour le formulaire de création
    if (createForm) {
        createForm.addEventListener('submit', function (event) {
            let isValid = true;
            const errors = [];

            // Vérification des champs texte
            const departure = createForm.querySelector('input[name="offer_travel[departure]"]');
            const destination = createForm.querySelector('input[name="offer_travel[destination]"]');
            const hotelName = createForm.querySelector('input[name="offer_travel[hotel_name]"]');
            const flightName = createForm.querySelector('input[name="offer_travel[flight_name]"]');
            const description = createForm.querySelector('textarea[name="offer_travel[discription]"]');
            const price = createForm.querySelector('input[name="offer_travel[price]"]');
            const category = createForm.querySelector('select[name="offer_travel[category]"]');
            const agency = createForm.querySelector('select[name="offer_travel[agency]"]');
            const departureDate = createForm.querySelector('input[name="offer_travel[departure_date]"]');
            const arrivalDate = createForm.querySelector('input[name="offer_travel[arrival_date]"]');
            const imageFile = createForm.querySelector('input[name="offer_travel[imageFile]"]');

            // Validation du champ Départ
            if (!departure.value || departure.value.length < 2) {
                errors.push('Le champ Départ doit contenir au moins 2 caractères.');
                isValid = false;
            }

            // Validation du champ Destination
            if (!destination.value || destination.value.length < 2) {
                errors.push('Le champ Destination doit contenir au moins 2 caractères.');
                isValid = false;
            }

            // Validation du champ Nom de l'hôtel
            if (!hotelName.value || hotelName.value.length < 2) {
                errors.push('Le nom de l\'hôtel doit contenir au moins 2 caractères.');
                isValid = false;
            }

            // Validation du champ Nom du vol
            if (!flightName.value || flightName.value.length < 2) {
                errors.push('Le nom du vol doit contenir au moins 2 caractères.');
                isValid = false;
            }

            // Validation du champ Description
            if (!description.value || description.value.length < 10) {
                errors.push('La description doit contenir au moins 10 caractères.');
                isValid = false;
            }

            // Validation du champ Prix
            if (!price.value || price.value <= 0) {
                errors.push('Le prix doit être supérieur à 0.');
                isValid = false;
            }

            // Validation du champ Catégorie
            if (!category.value) {
                errors.push('Veuillez sélectionner une catégorie.');
                isValid = false;
            }

            // Validation du champ Agence
            if (!agency.value) {
                errors.push('Veuillez sélectionner une agence.');
                isValid = false;
            }

            // Validation des dates
            const today = new Date().toISOString().split('T')[0];
            if (!departureDate.value || departureDate.value < today) {
                errors.push('La date de départ doit être aujourd\'hui ou dans le futur.');
                isValid = false;
            }
            if (!arrivalDate.value) {
                errors.push('La date d\'arrivée est obligatoire.');
                isValid = false;
            }
            if (departureDate.value && arrivalDate.value && departureDate.value > arrivalDate.value) {
                errors.push('La date de départ doit être antérieure ou égale à la date d\'arrivée.');
                isValid = false;
            }

            // Validation de l'image
            if (imageFile.files.length > 0) {
                const file = imageFile.files[0];
                const maxSize = 2 * 1024 * 1024; // 2 Mo
                const validTypes = ['image/jpeg', 'image/png'];
                if (!validTypes.includes(file.type)) {
                    errors.push('L\'image doit être au format JPEG ou PNG.');
                    isValid = false;
                }
                if (file.size > maxSize) {
                    errors.push('L\'image ne doit pas dépasser 2 Mo.');
                    isValid = false;
                }
            }

            if (!isValid) {
                event.preventDefault();
                alert('Veuillez corriger les erreurs suivantes :\n\n' + errors.join('\n'));
            }
        });
    }

    // Validation pour le formulaire d'édition (même logique)
    if (editForm) {
        editForm.addEventListener('submit', function (event) {
            let isValid = true;
            const errors = [];

            // Réutilisation de la même logique de validation
            const departure = editForm.querySelector('input[name="offer_travel[departure]"]');
            const destination = editForm.querySelector('input[name="offer_travel[destination]"]');
            const hotelName = editForm.querySelector('input[name="offer_travel[hotel_name]"]');
            const flightName = editForm.querySelector('input[name="offer_travel[flight_name]"]');
            const description = editForm.querySelector('textarea[name="offer_travel[discription]"]');
            const price = editForm.querySelector('input[name="offer_travel[price]"]');
            const category = editForm.querySelector('select[name="offer_travel[category]"]');
            const agency = editForm.querySelector('select[name="offer_travel[agency]"]');
            const departureDate = editForm.querySelector('input[name="offer_travel[departure_date]"]');
            const arrivalDate = editForm.querySelector('input[name="offer_travel[arrival_date]"]');
            const imageFile = editForm.querySelector('input[name="offer_travel[imageFile]"]');

            if (!departure.value || departure.value.length < 2) {
                errors.push('Le champ Départ doit contenir au moins 2 caractères.');
                isValid = false;
            }

            if (!destination.value || destination.value.length < 2) {
                errors.push('Le champ Destination doit contenir au moins 2 caractères.');
                isValid = false;
            }

            if (!hotelName.value || hotelName.value.length < 2) {
                errors.push('Le nom de l\'hôtel doit contenir au moins 2 caractères.');
                isValid = false;
            }

            if (!flightName.value || flightName.value.length < 2) {
                errors.push('Le nom du vol doit contenir au moins 2 caractères.');
                isValid = false;
            }

            if (!description.value || description.value.length < 10) {
                errors.push('La description doit contenir au moins 10 caractères.');
                isValid = false;
            }

            if (!price.value || price.value <= 0) {
                errors.push('Le prix doit être supérieur à 0.');
                isValid = false;
            }

            if (!category.value) {
                errors.push('Veuillez sélectionner une catégorie.');
                isValid = false;
            }

            if (!agency.value) {
                errors.push('Veuillez sélectionner une agence.');
                isValid = false;
            }

            const today = new Date().toISOString().split('T')[0];
            if (!departureDate.value || departureDate.value < today) {
                errors.push('La date de départ doit être aujourd\'hui ou dans le futur.');
                isValid = false;
            }
            if (!arrivalDate.value) {
                errors.push('La date d\'arrivée est obligatoire.');
                isValid = false;
            }
            if (departureDate.value && arrivalDate.value && departureDate.value > arrivalDate.value) {
                errors.push('La date de départ doit être antérieure ou égale à la date d\'arrivée.');
                isValid = false;
            }

            if (imageFile.files.length > 0) {
                const file = imageFile.files[0];
                const maxSize = 2 * 1024 * 1024; // 2 Mo
                const validTypes = ['image/jpeg', 'image/png'];
                if (!validTypes.includes(file.type)) {
                    errors.push('L\'image doit être au format JPEG ou PNG.');
                    isValid = false;
                }
                if (file.size > maxSize) {
                    errors.push('L\'image ne doit pas dépasser 2 Mo.');
                    isValid = false;
                }
            }

            if (!isValid) {
                event.preventDefault();
                alert('Veuillez corriger les erreurs suivantes :\n\n' + errors.join('\n'));
            }
        });
    }
});

// Fonction pour confirmer la suppression
function confirmDelete() {
    return confirm('Êtes-vous sûr de vouloir supprimer cette offre ? Cette action est irréversible.');
}