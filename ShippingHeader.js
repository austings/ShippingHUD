<script>
// COUNTDOWN SCRIPT - Set the date we're counting down to
var currentDate = new Date(new Date().getTime());
var hours = currentDate.getHours();
var countDownDate = new Date(currentDate);
countDownDate.setMinutes(0);
countDownDate.setSeconds(0);
countDownDate.setHours(17);

if(hours >= 17 ){
    countDownDate.setDate(countDownDate.getDate() + 1);
}

// Update the count down every 1 second
var x = setInterval(function() {

  // Get today's date and time
  var now = new Date().getTime();
    
  // Find the distance between now and the count down date
  var distance = countDownDate - now;
    
  // Time calculations for days, hours, minutes and seconds
  var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
  var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
  var seconds = Math.floor((distance % (1000 * 60)) / 1000);
  
  if(hours.toString().length<2){
  	hours = "0"+hours.toString();}
  if(minutes.toString().length<2){
  	minutes = "0"+minutes.toString();}
  if(seconds.toString().length<2){
  	seconds = "0"+seconds.toString();}
    
  // Output the result in an element with id
  var oldH = document.getElementById("hour").innerHTML;
  var oldM = document.getElementById("min").innerHTML;
  var oldS = document.getElementById("sec").innerHTML;
  if(oldH!=hours &&hours>=0)
  {
  	document.getElementById("hour").innerHTML = hours;
  }
  if(oldM!=" "+minutes+" "&&minutes>=0)
  {
  	document.getElementById("min").innerHTML = " "+ minutes +" ";
  }
  if(oldS!=seconds&&seconds>=0)
  {
  	document.getElementById("sec").innerHTML =  seconds;
  }
    

}, 250);
</script>

<script>
//UPDATE THE SHIPPING DATE WHEN CLOCK TICKS OVER
var days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
var now = new Date();
var time = parseInt(now.getHours()+""+now.getMinutes());
var day = days[ now.getDay()];
if(day=='Sunday'|day=='Saturday' &time>1700)
day = 'Monday'
else
{
if(time<=1700){
day = days[ now.getDay() ];}
else{
day = days[ now.getDay()+1 ];}
}
document.getElementById('day').innerHTML = "Order before 5:00PM ET (2:00PM PT) to ship "+day+".";
document.getElementById('dayMobile').innerHTML = "Order before 5:00PM ET (2:00PM PT) to ship "+day+".";
</script>
