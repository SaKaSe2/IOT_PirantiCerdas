function loadData() {
  fetch("api/get_latest.php")
    .then(res => res.json())
    .then(data => {
      document.getElementById("suhu").innerText = data.suhu;
      document.getElementById("hum").innerText = data.kelembaban;
      document.getElementById("kipas").innerText = data.kipas;

      const ledEl = document.getElementById("led");
      ledEl.innerText = data.led;

      // Reset class
      ledEl.className = "";

      if (data.led === "GREEN") {
        ledEl.classList.add("green");
      } else if (data.led === "YELLOW") {
        ledEl.classList.add("yellow");
      } else if (data.led === "RED") {
        ledEl.classList.add("red");
      }
    });
}

function send(mode,fan){
  fetch("api/control.php",{
    method:"POST",
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:`mode=${mode}&fan=${fan}&led=ON`
  });
}

function fanOn(){ send(mode.value,"ON"); }
function fanOff(){ send(mode.value,"OFF"); }

setInterval(loadData,2000);
