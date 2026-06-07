function calcul() {
  let ent = document.getElementById("entier").value;
  let resultat = "";
  for (let i = 1; i <= 10; i++) {
    resultat += ent + " x " + i + "=" + ent * i + "<br>";
  }
  document.getElementById("res").innerHTML = resultat;
}
