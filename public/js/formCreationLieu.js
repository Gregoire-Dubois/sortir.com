document.addEventListener("DOMContentLoaded", function() {
    let sortieFormData = new FormData();

    // Affichage du formulaire dans la modale
    document.getElementById("add_place_button").addEventListener("click", function(e) {
        e.preventDefault();
        // Effectuez une requête AJAX pour récupérer le contenu du formulaire
        let xhr = new XMLHttpRequest();

        xhr.open("GET", "/sorties/lieu/afficher");

        xhr.onload = function() {
            if (xhr.status === 200) {
                document.getElementById("popup_add_place").innerHTML = xhr.responseText;
                // Affichez la modale
                document.getElementById("popup_add_place").style.display = "block";
                document.getElementById("popup_overlay").style.display = "block";

            } else {
                console.error("Erreur lors de la requête AJAX");
            }
        };
        xhr.send();
    });

    // Enregistrement des données
    document.addEventListener("click", function(e) {
        if (e.target.id === "confirm_add_place_button") {
            e.preventDefault();
            let formData = new FormData(document.getElementById("form_lieu"));
            let xhr = new XMLHttpRequest();

            xhr.open("POST", "/sorties/lieu/creation");
            xhr.onload = function() {
                if (xhr.status === 200) {
                    // Cacher la modale après l'enregistrement
                    document.getElementById("popup_add_place").style.display = "none";
                    document.getElementById("popup_overlay").style.display = "none";

                    // Mise à jour du formulaire de sortie avec le nouveau lieu
                    let response = JSON.parse(xhr.responseText);
                    let lieuSelect = document.getElementById("sortie_lieu");

                    // Créer une nouvelle option pour le nouveau lieu
                    let newOption = document.createElement("option");
                    newOption.value = response.id;
                    newOption.text = response.nom;
                    lieuSelect.appendChild(newOption);

                    // Sélectionner le nouveau lieu dans le formulaire de sortie
                    newOption.selected = true;

                    // Réaffecter les données du formulaire de sortie
                    for (let pair of sortieFormData.entries()) {
                        let key = pair[0];
                        let value = pair[1];
                        if (key !== "lieu") {
                            document.getElementById("form_sortie_" + key).value = value;
                        }
                    }
                } else {
                    console.error("Erreur lors de la soumission du formulaire en AJAX");
                }
            };
            xhr.send(formData);
        }
    });

    // Cacher la modale lorsque l'utilisateur clique sur le bouton "Fermer" ou en dehors de la modale
    document.addEventListener("click", function(e) {
        //Si on clique sur annuler, la modale se ferme
        if (e.target.id === 'cancel_add_place_button') {
            e.preventDefault();
            document.getElementById("popup_add_place").style.display = "none";
            document.getElementById("popup_overlay").style.display = "none";

            // Réaffecter les données déjà saisies du formulaire de sortie
            for (let pair of sortieFormData.entries()) {
                let key = pair[0];
                let value = pair[1];
                if (key !== "lieu") {
                    document.getElementById("form_sortie_" + key).value = value;
                }
            }
        }
    });
});