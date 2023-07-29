window.onload = () => {
    let sortie_ville = document.querySelector("#sortie_ville");
    let codePostalid = document.querySelector("#event_postal_code")
    console.log(sortie_ville)
    console.log(codePostalid)
    sortie_ville.addEventListener("change", function () {
        let form = this.closest("form");
        let data = this.name + "=" + this.value;
        console.log(data)
        fetch(form.action, {
            method: form.getAttribute("method"),
            body: data,
            headers: {
                "Content-Type": "application/x-www-form-urlencoded; charset:UTF-8"
            }
        })
            .then(response => response.text())
            .then(html => {
                let content = document.createElement("html");
                content.innerHTML = html;
                let nouveauSelect = content.querySelector("#sortie_lieu");
                document.querySelector("#sortie_lieu").replaceWith(nouveauSelect);
            })
        fetch('/get-code-postal/' + sortie_ville.value)
            .then(response => response.json())
            .then(codePostal => {
                codePostalid.value = codePostal
                document.querySelector("#event_postal_code").textContent = codePostal
                console.log(codePostal)})
            .catch(error =>
                console.log(error)
            )
    });
}