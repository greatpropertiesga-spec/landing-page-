<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sell Your House Fast</title>
<style>
body{
margin:0;
font-family:Arial;
color:white;
background:url('https://images.unsplash.com/photo-1600585154340-be6161a56a0c') center/cover no-repeat;
}
.overlay{
background:rgba(0,0,0,0.6);
padding:60px;
}
h1{font-size:48px;}
.sub{color:#ffd700;font-size:24px;}
.form{
background:white;
color:black;
padding:20px;
border-radius:10px;
max-width:350px;
}
input{
width:100%;
padding:10px;
margin-bottom:10px;
}
button{
background:red;
color:white;
padding:12px;
width:100%;
border:none;
font-weight:bold;
}
</style>
</head>
<body>
<div class="overlay">
<h1>Sell Your House Fast in Georgia</h1>
<p class="sub">Get a Fair Cash Offer in 24 Hours</p>
<div class="form">
<form id="leadForm">
<input name="address" placeholder="Property Address" required>
<input name="name" placeholder="Full Name" required>
<input name="phone" placeholder="Phone Number" required>
<input name="email" placeholder="Email" required>
<button>GET MY CASH OFFER</button>
</form>
</div>
</div>
<script>
document.getElementById('leadForm').addEventListener('submit', async function(e){
e.preventDefault();
let data = new FormData(this);
await fetch('save_lead.php',{method:'POST',body:data});
alert('Submitted');
});
</script>
</body>
</html>