document.addEventListener("DOMContentLoaded", function () {
    let sortieFormData = new FormData();

    // Affichage du formulaire dans la modale
    document.getElementById("add_city_button").addEventListener("click", function (e) {
        e.preventDefault();

        // Effectuez une requête AJAX pour récupérer le contenu du formulaire
        let xhr = new XMLHttpRequest();

        xhr.open("GET", "/sorties/ville/afficher");

        xhr.onload = function () {
            if (xhr.status === 200) {
                // Ajoutez le contenu de la modale dans le DOM
                //console.log(xhr.responseText)
                //console.log(xhr.status)
                document.getElementById("popup_add_city").innerHTML = xhr.responseText;
                //console.log(document.getElementById("popup_add_place").innerHTML)
                // Affichez la modale
                document.getElementById("popup_add_city").style.display = "block";

            } else {
                console.error("Erreur lors de la requête AJAX");
            }
        };
        xhr.send();
    });

    // Enregistrement des données
    document.addEventListener("click", function (e) {
        if (e.target.id === "confirm_add_city_button") {
            e.preventDefault();
            let formData = new FormData(document.getElementById("form_ville"));
            let xhr = new XMLHttpRequest();

            xhr.open("POST", "/sorties/ville/creation");
            xhr.onload = function () {
                if (xhr.status === 200) {
                    //console.log("Données enregistrées avec succès !");

                    // Cacher la modale après l'enregistrement
                    document.getElementById("popup_add_city").style.display = "none";

                    // Mise à jour du formulaire de sortie avec la nouvelle ville
                    // Timeout pour laisser le temps à la base de créer comme il faut. Peut-être pas nécessaire
                    setTimeout(function () {
                        //Les valeurs de retour à capter sont à définir dans le tableau json dans le controller
                        let response = JSON.parse(xhr.responseText);
                        let lieuSelect = document.getElementById("sortie_ville");

                        // Créer une nouvelle option dans le sélecteur pour la nouvelle ville
                        let newOption = document.createElement("option");
                        newOption.value = response.id;
                        newOption.text = response.nom;
                        lieuSelect.appendChild(newOption);

                        // Sélectionner la nouvelle ville dans le formulaire de sortie
                        newOption.selected = true;

                        // Réaffecter les données du formulaire de sortie
                        for (let pair of sortieFormData.entries()) {
                            let key = pair[0];
                            let value = pair[1];
                            if (key !== "lieu") {
                                document.getElementById("form_sortie_" + key).value = value;
                            }
                        }
                    }, 500);}
                else
                    {
                        console.error("Erreur lors de la soumission du formulaire en AJAX");
                    }
                }
                ;
                xhr.send(formData);
            }
        });

        // Cacher la modale lorsque l'utilisateur clique sur le bouton "Fermer" ou en dehors de la modale
        document.addEventListener("click", function (e) {
            //Si on clique sur annuler, la modale se ferme
            if (e.target.id === 'cancel_add_city_button') {
                e.preventDefault();
                document.getElementById("popup_add_city").style.display = "none";

                // Réaffecter les données déjà saisies du formulaire de sortie
                for (let pair of sortieFormData.entries()) {
                    let key = pair[0];
                    let value = pair[1];
                    if (key !== "ville") {
                        document.getElementById("form_sortie_" + key).value = value;
                    }
                }
            }
        });
    });