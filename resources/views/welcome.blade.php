<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">

    <style>
        .user-message {
            border: 1px solid #d9d9d9;
            background-color: #E8E9EC;
            border-radius: 15px;
        }
        .bot-message {
            border: 1px solid #90a4ae;
            background-color: #b0bec5;
            border-radius: 15px;
        }

    </style>

    <title>Hello, world!</title>
  </head>
  <body>

    <div class="container">

        <div class="chat-container">

        </div>

        <div class="row">
            <div class="col-12">
                <textarea name="" id="message" cols="30" rows="10" class="form-control"></textarea>
            </div>
            <div class="col-12">
                <button class="btn btn-primary" id="send">
                    Send
                </button>
            </div>
        </div>
    </div>

    <script
  src="https://code.jquery.com/jquery-3.6.0.min.js"
  integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
  crossorigin="anonymous"></script>

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js" integrity="sha384-JEW9xMcG8R+pH31jmWH6WWP0WintQrMb4s7ZOdauHnUtxwoG2vI5DkLtS3qm9Ekf" crossorigin="anonymous"></script>

    <!-- Option 2: Separate Popper and Bootstrap JS -->
    <!--
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js" integrity="sha384-SR1sx49pcuLnqZUnnPwx6FCym0wLsk5JZuNx2bPPENzswTNFaQU1RDvt3wT4gWFG" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.min.js" integrity="sha384-j0CNLUeiqtyaRmlzUHCPZ+Gy5fQu0dQ6eZ/xAww941Ai1SxSY+0EQqNXNE6DZiVc" crossorigin="anonymous"></script>
    -->

    <script>

        $(document).ready(function(){

            var status = 0;
            var flow = "new";

            var name="";
            var email="";
            var password="";

            var balanceCurrency="";

            var depositCurrency="";
            var depositAmount="";
            
            var withdrawCurrency="";
            var withdrawAmount="";

            $.get('/checkStatus', function(callback){

                let message = "";

                if(callback.result){

                    message = callback.name;

                }else {

                    message = "Hello! I'm LuccasBot, welcome to this chatbot example! Would you like to:<br>";
                    message += "<br><strong>1</strong> - Register";
                    message += "<br><strong>2</strong> - Log In";

                }

                $('.chat-container').append('<div class="row"><div class="col-12 bot-message my-3 py-3"><div class="float-start">'+message+'</div></div></div>');

            }, 'json');

            $(document).on('click', '#send', function(){

                $('.chat-container').append('<div class="row"><div class="col-12 user-message my-3 py-3"><div class="float-end">'+$("#message").val()+'</div></div></div>');

                handleMessage($("#message").val().toLowerCase());

                $("#message").val('');

            });

            function handleMessage(message){

                switch(flow){
                    case "new":
                        switch(status){
                            case 0:
                                switch(message){
                                    case '1':
                                    case 'register':
                                        sendMessage("What is your name?");
                                        flow="register-name";
                                    break;
                                    case '2':
                                    case 'login':
                                    case 'log in':
                                        sendMessage("What is your email?");
                                        flow="login-email";
                                    break;
                                }
                            break;
                        }
                    break;
                    case "register-name":
                        name = message;
                        sendMessage("Thanks! Now, please insert an email address.");
                        flow="register-mail";
                    break;
                    case "register-mail":
                        email = message;
                        sendMessage("Thanks! Now, please insert a password.");
                        flow="register-pass";
                    break;
                    case "register-pass":
                        sendMessage("Let me create a new account for you!");
                        password = message;

                        $.post('/account', {
                            name: name,
                            email: email,
                            password: password,
                            currency: 'EUR',
                        }, function(callback){

                            if(callback.result){

                                sendMessage("Account created!");
                                flow="logged";
                                showLoggedMenu();
                            }

                        }, 'json');

                        
                    break;
                    case "login-email":
                        email = message;
                        sendMessage("Thanks! Now, please insert your password.");
                        flow="login-pass";
                    break;
                    case "login-pass":
                        sendMessage("Let me access your account");
                        password = message;

                        $.post('/login', {
                            email: email,
                            password: password,
                        }, function(callback){

                            if(callback.result){

                                sendMessage("Hello, "+callback.name+"!");
                                sendMessage("What would you like to do?<br><br><strong>1</strong> - Check current balance<br><strong>2</strong> - Deposit money<br><strong>3</strong> - Withdraw money");

                                status = 1;
                                flow="logged";
                            }

                        }, 'json');

                        flow="logged";
                    break;
                    case "logged":
                        switch(message){
                            case '1':
                            case 'balance':
                                sendMessage("What is the currency?");
                                flow="balance-currency";
                            break;
                            case '2':
                            case 'deposit':
                                sendMessage("What is the currency?");
                                flow="logged-deposit-currency";
                            break;
                            case '3':
                            case 'withdraw':
                                sendMessage("What is the currency?");
                                flow="logged-withdraw-currency";
                            break;
                        }
                    break;
                    case "balance-currency":
                        balanceCurrency = message;
                        $.post('/balance', { 
                                email: email,
                                password: password,
                                balanceCurrency: balanceCurrency.toUpperCase(),
                            },function(callback){

                                sendMessage("Your Current Balance is: " + balanceCurrency.toUpperCase() + " $" + callback.balance);

                                flow="logged";
                                showLoggedMenu();

                            }, 'json');
                    break;
                    case "logged-deposit-currency":
                        depositCurrency = message;
                        sendMessage("Thanks! Now, please insert the amount you want to deposit");
                        flow="logged-deposit-amount";
                    break;
                    case "logged-deposit-amount":
                        depositAmount = message;

                        $.post('/deposit', { 
                                email: email,
                                password: password,
                                depositCurrency: depositCurrency.toUpperCase(),
                                depositAmount: depositAmount,
                            },function(callback){

                                if(callback.result){
                                    sendMessage("Thanks! Deposit completed!");
                                }else{
                                    sendMessage("Sorry, error on deposit, please review your data and try again.");
                                }

                                flow="logged";
                                showLoggedMenu();

                            }, 'json');

                        flow="logged";
                    break;
                    case "logged-withdraw-currency":
                        withdrawCurrency = message;
                        sendMessage("Thanks! Now, please insert the amount you want to withdraw");
                        flow="logged-withdraw-amount";
                    break;
                    case "logged-withdraw-amount":
                        withdrawAmount = message;

                        $.post('/withdraw', { 
                                email: email,
                                password: password,
                                withdrawCurrency: withdrawCurrency.toUpperCase(),
                                withdrawAmount: withdrawAmount,
                            },function(callback){

                                if(callback.result){
                                    sendMessage("Thanks! Withdraw completed!");
                                }else{
                                    sendMessage("Sorry, error on withdraw, please review your data and try again.");
                                }

                                flow="logged";
                                showLoggedMenu();

                            }, 'json');

                        flow="logged";
                    break;

                    
                }



            }

            function showLoggedMenu(){
                sendMessage("What would you like to do?<br><br><strong>1</strong> - Check current balance<br><strong>2</strong> - Deposit money<br><strong>3</strong> - Withdraw money");
            }
            function showUnloggedMenu(){
                sendMessage("Hello! I'm LuccasBot, welcome to this chatbot example! Would you like to:<br><br><strong>1</strong> - Register<br><strong>2</strong> - Log In");
            }

            function sendMessage(message){
                $('.chat-container').append('<div class="row"><div class="col-12 bot-message my-3 py-3"><div class="float-start">'+message+'</div></div></div>');
            }

        });

    </script>
  </body>
</html>
