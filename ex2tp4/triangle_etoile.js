function triangle() {
  let hauteur = parseInt(document.getElementById("haut").value);
  let type1 = document.getElementById("type").value;
  let reverse1 = document.getElementById("reverse").checked;
  let ch = "";
  if (type1 === "droit") {
    if (!reverse1) {
      for (let i = 1; i <= hauteur; i++) {
        ch += "*".repeat(i) + "<br>";
      }
    } else {
      for (let i = hauteur; i >= 1; i--) {
        ch += "*".repeat(i) + "<br>";
      }
    }
  } else {
    if (!reverse1) {
      for (let i = 1; i <= hauteur; i++) {
        ch += "&nbsp".repeat(hauteur - i) + "*".repeat(i) + "<br>";
      }
    } else {
      for (let i = hauteur; i >= 1; i--) {
        ch += "&nbsp".repeat(hauteur - i) + "*".repeat(i) + "<br>";
      }
    }
  }
  document.getElementById("res").innerHTML = ch;
}
