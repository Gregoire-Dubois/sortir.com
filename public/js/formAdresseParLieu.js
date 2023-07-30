        let sortie_lieu = document.querySelector("#sortie_lieu");
        let rue_id = document.getElementById("event_street");
        console.log(sortie_lieu)
        console.log(rue_id)
        sortie_lieu.addEventListener("change", function () {
                let data = this.name + "=" + this.value;
                console.log(data)
                fetch('/get-rue/' + sortie_lieu.value)
                    .then(response => response.json())
                    .then(rue => {
                            rue_id.value = rue
                            document.querySelector("#event_street").textContent = rue
                            console.log(rue)
                    })
        })

