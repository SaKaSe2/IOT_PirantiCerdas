function loadData(){
  fetch("api/get_latest.php")
  .then(r=>r.json())
  .then(d=>{
    suhu.innerText = d.suhu;
    hum.innerText = d.kelembaban;
    kipas.innerText = d.kipas;
    led.innerText = d.led;
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
