function autocomplete(inp, arr, count, newIngredientLink) {
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
            const toto = new RegExp(val, "i");
            // Vérifie si l'objet parcouru commence comme la recherche
            if (value.search(toto) != -1 && found <= 5) {
                // Création d'un nouvel élément pour chaque résultat trouvé
                b = document.createElement("DIV");
                // Partie correspondante en gras
                b.innerHTML =
                    "<strong>" + value.substr(0, val.length) + "</strong>";
                b.innerHTML += value.substr(val.length);
                /*insert a input field that will hold the current array item's value:*/
                b.innerHTML += "<input type='hidden' value='" + value + "'>";
                // Ajout d'une fonction de remplissage automatique lors d'un click
                b.addEventListener("click", function (e) {
                    // Ajout de la valeur dans le champ
                    inp.value = this.getElementsByTagName("input")[0].value;
                    // Ajout de l'id de l'ingrédient dans le champ caché
                    let ingredientIdInput = document.getElementById(
                        "ingredientId" + count
                    );
                    // Définition de la valeur par l'id de l'ingrédient
                    ingredientIdInput.value = key;
                    // Fermeture de toutes les listes ouvertes
                    closeAllLists();
                });
                a.appendChild(b);
                found += 1;
            }
        });
        // Ajout d'une ligne pour proposer un ingrédient
        // Création d'un nouvel élément pour chaque résultat trouvé
        b = document.createElement("a");
        b.target = "_blank";
        b.href = newIngredientLink;
        // Partie correspondante en gras
        b.innerHTML = "<div class=\"text-veryummy-primary\"><strong>PROPOSER UN INGREDIENT</strong></div>";
        
        a.appendChild(b);
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
        // Touche entrée ou Tab
        else if (e.keyCode == 13 || e.keyCode == 9) {
            if (e.keyCode == 13) {
                e.preventDefault();
            }
            if (currentFocus > -1) {
                // Simulation du clic sur le résultat actif
                if (x) x[currentFocus].click();
            }
            // Si pas de résultat pré sélectionné
            else {
                // Si on a des résultats
                if (x.length > 1) {
                    // Select the first entry of the list
                    x[0].click();
                }
                // Si pas de résultats
                else {
                    // Reset de la valeur du champ
                    inp.value = null;
                    // Récupération du champ définissant l'id de l'aliment
                    let ingredientIdInput = document.getElementById(
                        "ingredientId" + count
                    );
                    // Reset de l'id à 0
                    ingredientIdInput.value = 0;
                    // Fermeture de toutes les listes ouvertes
                    closeAllLists();
                }
            }
        }
    });
    function addActive(x) {
        /*Pré sélectionner un résultat de la liste*/
        if (!x) return false;
        /*Retirer la pré sélection de toute la liste*/
        removeActive(x);
        if (currentFocus >= x.length) currentFocus = 0;
        if (currentFocus < 0) currentFocus = x.length - 1;
        /*Ajout de la classe "autocomplete-active":*/
        x[currentFocus].classList.add("autocomplete-active");
    }
    function removeActive(x) {
        /*Retirer la pré sélection de toute la liste*/
        for (var i = 0; i < x.length; i++) {
            x[i].classList.remove("autocomplete-active");
        }
    }
    function closeAllLists(elmnt) {
        /*Fermer les auto complete*/
        var x = document.getElementsByClassName("autocomplete-items");
        for (var i = 0; i < x.length; i++) {
            if (elmnt != x[i] && elmnt != inp) {
                x[i].parentNode.removeChild(x[i]);
            }
        }
    }
    /*Lorsque l'utilisateur quitte l'autocomplete:*/
    inp.addEventListener("blur", function (e) {
        closeAllLists(e.target);
    });
    /*Lorsque l'utilisateur clique ailleurs que sur la liste de résultat*/
    document.addEventListener("click", function (e) {
        closeAllLists(e.target);
    });
}
