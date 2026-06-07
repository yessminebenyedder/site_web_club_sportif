function reverse() {
  let res = document.getElementById("resultat1").value;
  let tab = res.split("");
  let ch = tab.reverse();
  let ch2 = ch.join("");
  document.getElementById("resultat").textContent = ch2;
}
