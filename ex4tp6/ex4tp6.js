function calculatrice() {
  let nb11 = parseFloat(document.getElementById("nb1").value);
  let nb22 = parseFloat(document.getElementById("nb2").value);
  let oper = document.getElementById("choix").value;
  let resultat = "";
  if (oper === "addition(+)") {
    resultat = nb11 + nb22;
  } else if (oper === "soustraction(-)") {
    resultat = nb11 - nb22;
  } else if (oper === "multiplication(*)") {
    resultat = nb11 * nb22;
  } else if (oper === "division(/)") {
    resultat = nb11 / nb22;
  }
  document.getElementById("res").value = resultat;
}
function changer() {
  let oper1 = document.getElementById("choix").value;
  let res = "+";
  if (oper1 === "addition(+)") {
    res = "+";
  } else if (oper1 === "soustraction(-)") {
    res = "-";
  } else if (oper1 === "multiplication(*)") {
    res = "*";
  } else if (oper1 === "division(/)") {
    res = "/";
  }
  document.getElementById("oper").innerHTML = res;
}
