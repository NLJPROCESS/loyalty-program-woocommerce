(function () {
    'use strict';

    // Créer un point d'entrée pour le script
    const loyaltyForegroundEntryPoint = document.createElement('div');
    loyaltyForegroundEntryPoint.id = 'loyalty-foreground-entry-point';
    document.body.appendChild(loyaltyForegroundEntryPoint);

    // Fonction pour gérer l'ajout de récompenses
    function handleAddReward() {
        const addRewardButton = document.querySelector('.loyalty-add-reward-button');
        if (addRewardButton) {
            addRewardButton.addEventListener('click', function (event) {
                event.preventDefault();

                // Récupérer les données du formulaire
                const rewardName = document.getElementById('loyalty-reward-name').value;
                const rewardType = document.getElementById('loyalty-reward-type').value;
                const rewardValue = document.getElementById('loyalty-reward-value').value;
                const pointsRequired = document.getElementById('loyalty-points-required').value;
                const minAmount = document.getElementById('loyalty-min-amount').value;

                // Valider les données
                if (!rewardName || !rewardType || !rewardValue || !pointsRequired || !minAmount) {
                    alert('Veuillez remplir tous les champs.');
                    return;
                }

                // Envoyer les données via AJAX
                const data = {
                    action: 'loyalty_add_reward',
                    reward_name: rewardName,
                    reward_type: rewardType,
                    reward_value: rewardValue,
                    points_required: pointsRequired,
                    min_amount: minAmount,
                    security: loyaltyProgramAjax.nonce, // Nonce pour la sécurité
                };

                fetch(loyaltyProgramAjax.ajax_url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams(data),
                })
                    .then(response => response.json())
                    .then(response => {
                        if (response.success) {
                            alert('Récompense ajoutée avec succès !');
                            window.location.reload(); // Recharger la page pour afficher la nouvelle récompense
                        } else {
                            alert('Erreur : ' + response.data);
                        }
                    })
                    .catch(error => {
                        console.error('Erreur AJAX :', error);
                        alert('Une erreur s\'est produite. Veuillez réessayer.');
                    });
            });
        }
    }

    // Fonction pour gérer l'échange de points
    function handleRedeemReward() {
        const redeemButtons = document.querySelectorAll('.loyalty-redeem-button');
        redeemButtons.forEach(button => {
            button.addEventListener('click', function (event) {
                event.preventDefault();

                const rewardId = button.dataset.rewardId;
                if (!rewardId) {
                    alert('Récompense invalide.');
                    return;
                }

                // Envoyer les données via AJAX
                const data = {
                    action: 'loyalty_redeem_reward',
                    reward_id: rewardId,
                    security: loyaltyProgramAjax.nonce, // Nonce pour la sécurité
                };

                fetch(loyaltyProgramAjax.ajax_url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams(data),
                })
                    .then(response => response.json())
                    .then(response => {
                        if (response.success) {
                            alert('Récompense échangée avec succès !');
                            window.location.reload(); // Recharger la page pour mettre à jour les points
                        } else {
                            alert('Erreur : ' + response.data);
                        }
                    })
                    .catch(error => {
                        console.error('Erreur AJAX :', error);
                        alert('Une erreur s\'est produite. Veuillez réessayer.');
                    });
            });
        });
    }

    // Initialiser les gestionnaires d'événements
    document.addEventListener('DOMContentLoaded', function () {
        handleAddReward();
        handleRedeemReward();
    });
})();