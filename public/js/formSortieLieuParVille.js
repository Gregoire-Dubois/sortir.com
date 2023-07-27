window.onload = () => {
    // On va chercher la ville
    let ville = document.querySelector("#sortie_ville");
    console.log(ville)
    ville.addEventListener("change", function(){
        let form = this.closest("form");
        let data = this.name + "=" + this.value;
        console.log(data)
        let method = form.getAttribute("method")
        console.log(method)
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
                console.log(content)
                content.innerHTML = html;
                let nouveauSelect = content.querySelector("#sortie_lieu");
                document.querySelector("#sortie_lieu").replaceWith(nouveauSelect);
            })
            .catch(error => {
                console.log(error);
            })
    });
}