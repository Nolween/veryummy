function autocomplete(inp, arr, count) {
    // 2 arguments dans la fonction, le texte de la recherche et le tableau des éléments possibles
    var currentFocus;
    // Lorsque l'on écrit dans le champ de recherche
    inp.addEventListener("input", function (e) {
        var a,
            b,
            i,
            val = this.value;
        var found = 0;
        // Ferme toutes les listes précédement ouvertes
        closeAllLists();
        if (!val) {
            return false;
        }
        currentFocus = -1;
        // Création d'une div qui contiendra les résultats
        a = document.createElement("DIV");
        // Définition d'attributs dans la liste
        a.setAttribute("id", this.id + "autocomplete-list");
        a.setAttribute("class", "autocomplete-items");
        // Ajout de la div en tant qu'enfant dans le contaier
        this.parentNode.appendChild(a);
        // Parcours des items de la liste (limite de 5 résultats)
        Object.entries(arr).forEach(([key, value]) => {
            // Vérifie si l'objet parcouru commence comme la recherche
            if (
                value.substr(0, val.length).toUpperCase() == val.toUpperCase() && found <= 5
            ) {
                // Création d'un nouvel élément pour chaque résultat trouvé
                b = document.createElement("DIV");
                // Partie correspondante en gras
                b.innerHTML =
                    "<strong>" + value.substr(0, val.length) + "</strong>";
                b.innerHTML += value.substr(val.length);
                /*insert a input field that will hold the current array item's value:*/
                b.innerHTML += "<input type='hidden' value='" + value + "'>";
                // Ajoutd'une fonction de remplissage automatique lors d'un click
                b.addEventListener("click", function (e) {
                    // Ajout de la valeur dans le champ
                    inp.value = this.getElementsByTagName("input")[0].value;
                    // Ajout de l'id de l'ingrédient dans le champ caché
                    let ingredientIdInput = document.getElementById('ingredientId' + count)
                    // Définition de la valeur par l'id de l'ingrédient
                    ingredientIdInput.value = key
                    // Fermeture de toutes les listes ouvertes
                    closeAllLists();
                });
                a.appendChild(b);
                found += 1;
            }
        });
    });
    // Lorsque l'on appuie sur une touche du clavier
    inp.addEventListener("keydown", function (e) {
        var x = document.getElementById(this.id + "autocomplete-list");
        if (x) x = x.getElementsByTagName("div");
        //  Flèche du bas
        if (e.keyCode == 40) {
            currentFocus++;
            // Changement de résultat sélectionné
            addActive(x);
        } 
        // Flèche du haut
        else if (e.keyCode == 38) {
            currentFocus--;
            // Changement de résultat sélectionné
            addActive(x);
        } 
        // Touche entrée
        else if (e.keyCode == 13) {
            e.preventDefault();
            if (currentFocus > -1) {
                // Simulation du clic sur le résultat actif
                if (x) x[currentFocus].click();
            }
        }
    });
    function addActive(x) {
        /*a function to classify an item as "active":*/
        if (!x) return false;
        /*start by removing the "active" class on all items:*/
        removeActive(x);
        if (currentFocus >= x.length) currentFocus = 0;
        if (currentFocus < 0) currentFocus = x.length - 1;
        /*add class "autocomplete-active":*/
        x[currentFocus].classList.add("autocomplete-active");
    }
    function removeActive(x) {
        /*a function to remove the "active" class from all autocomplete items:*/
        for (var i = 0; i < x.length; i++) {
            x[i].classList.remove("autocomplete-active");
        }
    }
    function closeAllLists(elmnt) {
        /*close all autocomplete lists in the document,
              except the one passed as an argument:*/
        var x = document.getElementsByClassName("autocomplete-items");
        for (var i = 0; i < x.length; i++) {
            if (elmnt != x[i] && elmnt != inp) {
                x[i].parentNode.removeChild(x[i]);
            }
        }
    }
    /*execute a function when someone clicks in the document:*/
    document.addEventListener("click", function (e) {
        closeAllLists(e.target);
    });
}
