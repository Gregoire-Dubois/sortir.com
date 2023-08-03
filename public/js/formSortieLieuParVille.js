$(document).ready(function () {
    let sortie_ville = $("#sortie_ville");
    let sortie_lieu = $("#sortie_lieu");
    let codePostalid = $("#event_postal_code");
    let rue_id = $("#event_street");
    let latitude_id = $("#event_latitude");
    let longitude_id = $("#event_longitude");

    console.log(sortie_ville);
    console.log(codePostalid);
    console.log(sortie_lieu);
    console.log(rue_id);

    sortie_ville.on("change", function () {
        let villeChoisie = $(this).val();
        let form = $(this).closest("form");
        let data = $(this).attr("name") + "=" + $(this).val();
        console.log(data);
        if (villeChoisie === '') {
            codePostalid.val('');
            codePostalid.text('');
            sortie_lieu.val('');
            sortie_lieu.text('');

        } else {
            $.ajax({
                url: form.attr("action"),
                //method: form.attr("method"),
                method: "POST",
                data: data,
                contentType: "application/x-www-form-urlencoded; charset=UTF-8",
                success: function (html) {
                    let content = $("<html>").html(html);
                    let nouveauSelect = content.find("#sortie_lieu");
                    $("#sortie_lieu").replaceWith(nouveauSelect);
                }
            });

            $.ajax({
                url: '/get-code-postal/' + $(this).val(),
                dataType: 'json',
                success: function (codePostal) {
                    codePostalid.val(codePostal);
                    $("#event_postal_code").text(codePostal);
                    console.log(codePostal);
                },
                error: function (error) {
                    console.log(error);
                },
            });
        }
    });

    //Le sélecteur #sortie_lieu étant modifié, il faut mettre l'écoute sur un élément de plus haut niveau
    //sortie_lieu.on("change") ne peut pas fonctionner !
    $(document).on("change", "#sortie_lieu", function () {
        let data = $(this).attr("name") + "=" + $(this).val();
        console.log(data);
        if ($(this).val() === '') {
            rue_id.text('');
            latitude_id.text('');
            longitude_id.text('');
        } else {
            $.ajax({
                url: '/get-rue/' + $(this).val(),
                dataType: 'json',
                success: function (response) {
                    rue_id.text(response.rue);
                    latitude_id.text(response.latitude);
                    longitude_id.text(response.longitude);
                    //$("#event_street").text(rue);
                    console.log(response);
                },
                error: function (error) {
                    console.log(error);
                },
            });
        }
    });
});