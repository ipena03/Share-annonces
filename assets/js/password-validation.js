document.addEventListener('DOMContentLoaded', function() {
    // Sélectionner le champ de mot de passe (en supposant que l'ID généré par Symfony est 'registration_form_password_first')
    const passwordField = document.querySelector('#registration_form_password_first') || 
                          document.querySelector('[name="registration_form[password][first]"]');
    
    if (!passwordField) return; // Sortir si le champ n'est pas trouvé
    
    // Créer un conteneur pour les critères de validation
    const validationContainer = document.createElement('div');
    validationContainer.className = 'password-validation-container mt-2';
    passwordField.parentNode.insertBefore(validationContainer, passwordField.nextSibling);
    
    // Définir les critères de validation
    const criteria = [
        { id: 'length', text: 'Au moins 12 caractères', regex: /.{12,}/ },
        { id: 'uppercase', text: 'Au moins une majuscule', regex: /[A-Z]/ },
        { id: 'number', text: 'Au moins un chiffre', regex: /[0-9]/ },
        { id: 'special', text: 'Au moins un caractère spécial', regex: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/ }
    ];
    
    // Créer les éléments HTML pour chaque critère
    criteria.forEach(criterion => {
        const criterionElement = document.createElement('div');
        criterionElement.className = 'criterion d-flex align-items-center mb-1';
        criterionElement.id = `criterion-${criterion.id}`;
        
        // Icône pour indiquer l'état de validation (utilisation de Bootstrap icons si disponible)
        const icon = document.createElement('span');
        icon.className = 'criterion-icon me-2 text-danger';
        icon.innerHTML = '&#10007;'; // X symbol
        
        // Texte du critère
        const text = document.createElement('span');
        text.className = 'criterion-text';
        text.textContent = criterion.text;
        
        criterionElement.appendChild(icon);
        criterionElement.appendChild(text);
        validationContainer.appendChild(criterionElement);
    });
    
    // Fonction pour vérifier les critères
    function checkPasswordCriteria(password) {
        criteria.forEach(criterion => {
            const element = document.getElementById(`criterion-${criterion.id}`);
            const icon = element.querySelector('.criterion-icon');
            
            if (criterion.regex.test(password)) {
                icon.className = 'criterion-icon me-2 text-success';
                icon.innerHTML = '&#10003;'; // Checkmark symbol
            } else {
                icon.className = 'criterion-icon me-2 text-danger';
                icon.innerHTML = '&#10007;'; // X symbol
            }
        });
    }
    
    // Écouter les modifications du champ de mot de passe
    passwordField.addEventListener('input', function() {
        checkPasswordCriteria(this.value);
    });
    
    // Ajouter du CSS pour le style
    const style = document.createElement('style');
    style.textContent = `
        .password-validation-container {
            border-left: 3px solid #ddd;
            padding-left: 10px;
            margin-bottom: 15px;
        }
        .criterion-icon {
            font-size: 16px;
            font-weight: bold;
        }
        .text-success {
            color: #28a745;
        }
        .text-danger {
            color: #dc3545;
        }
    `;
    document.head.appendChild(style);
});