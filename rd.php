<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Techno White Fang</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap");

        *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body{
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(90deg, #e2e2e2, #c9d6ff);
            overflow: hidden;
        }

        .container{
            position: relative;
            width : 850px;
            height : 550px;
            background : #fff;
            border-radius : 30px;
            box-shadow : 0 0 30px rgba(0, 0, 0, .2);
            overflow: hidden;
            margin: 20px;
            transition: transform 0.3s ease;
        }

        .container:hover {
            transform: translateY(-5px);
        }

        .form-box{
            position: absolute;
            right: 0;
            width: 50%;
            height: 100%;
            background: #fff;
            display: flex;
            align-items: center;
            color: #333;
            text-align: center;
            padding: 40px;
            z-index: 1;
            transition: .6s ease-in-out 1.2s, visibility 0s 1s;
        }

        .container.active .form-box{
            right: 50%;
        }

        .form-box.register{
            visibility: hidden;
        }

        .container.active .form-box.register{
            visibility: visible;
        }

        form{
            width: 100%;
        }

        .container h1{
            font-size: 36px;
            margin: -10px 0;
            position: relative;
            display: inline-block;
        }

        .container h1::after {
            content: '';
            position: absolute;
            width: 0;
            height: 3px;
            background: #7494ec;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            transition: width 0.5s ease;
        }

        .container h1:hover::after {
            width: 70%;
        }

        .input-box{
            position: relative;
            margin: 30px 0;
            overflow: hidden;
        }

        .input-box input{
            width: 100%;
            padding: 13px 50px 13px 20px;
            background: #eee;
            border-radius: 8px;
            border: none;
            outline: none;
            font-size: 16px;
            color: #333;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .input-box input:focus {
            box-shadow: 0 0 8px rgba(116, 148, 236, 0.5);
            background: #f5f5f5;
        }

        .input-box input::placeholder {
            color: #888;
            font-weight: 400;
        }

        .input-box i{
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
            transition: all 0.3s ease;
        }

        .input-box input:focus + i {
            color: #7494ec;
            transform: translateY(-50%) scale(1.1);
        }

        .forgot-link{
            margin: -15px 0 15px;
            text-align: right;
        }

        .forgot-link a{
            font-size: 14.5px;
            color: #333;
            text-decoration: none;
            transition: color 0.3s ease;
            display: inline-block;
        }

        .forgot-link a:hover {
            color: #7494ec;
            transform: translateX(3px);
        }

        .forgot-link a i {
            margin-right: 5px;
            font-size: 16px;
        }

        .btn{
            width: 100%;
            height: 48px;
            background: #7494ec;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, .1);
            border: none;
            cursor: pointer;
            font-size: 16px;
            color: #fff;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            left: -100%;
            top: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn:hover {
            background: #5f7fd6;
            transform: translateY(-2px);
            box-shadow: 0 7px 14px rgba(116, 148, 236, 0.3);
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn:active {
            transform: translateY(1px);
        }

        .container p{
            font-size: 14.5px;
            margin: 15px 0;
            position: relative;
        }

        .container p::before,
        .container p::after {
            content: '';
            position: absolute;
            width: 35%;
            height: 1px;
            background: #ccc;
            top: 50%;
        }

        .container p::before {
            left: 0;
        }

        .container p::after {
            right: 0;
        }

        .social-icons{
            display: flex;
            justify-content: center;
        }

        .social-icons a{
            display: inline-flex;
            justify-content: center;
            align-items: center;
            padding: 10px;
            border: 2px solid #ccc;
            border-radius: 8px;
            font-size: 24px;
            color: #333;
            text-decoration: none;
            margin: 0 8px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .social-icons a::before {
            content: '';
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            height: 100%;
            background: #7494ec;
            transition: all 0.3s ease;
            z-index: -1;
        }

        .social-icons a:hover {
            color: #fff;
            border-color: #7494ec;
            transform: translateY(-5px);
        }

        .social-icons a:hover::before {
            top: 0;
        }

        .social-icons a i {
            transition: transform 0.3s ease;
        }

        .social-icons a:hover i {
            transform: scale(1.2);
        }

        .toggle-box{
            position: absolute;
            width: 100%;
            height: 100%;
        }

        .toggle-box::before{
            content: '';
            position: absolute;
            left: -250%;
            width: 300%;
            height: 100%;
            background: linear-gradient(135deg, #7494ec, #5a7ae0);
            border-radius: 150px;
            z-index: 2;
            transition: 1.8s ease-in-out;
        }

        .container.active .toggle-box::before{
            left: 50%;
        }

        .toggle-panel{
            position: absolute;
            width: 50%;
            height: 100%;
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 2;
            transition: .6s ease-in-out;
        }

        .toggle-panel.toggle-left{
            left: 0;
            transition-delay: 1.2s;
        }

        .container.active .toggle-panel.toggle-left{
            left: -50%;
            transition-delay: .6s;
        }

        .toggle-panel.toggle-right{
            right: -50%;
            transition-delay: .6s;
        }

        .container.active .toggle-panel.toggle-right{
            right: 0;
            transition-delay: 1.2s;
        }

        .toggle-panel h1{
            font-size: 42px;
            margin-bottom: 15px;
            position: relative;
        }

        .toggle-panel h1::after {
            content: '';
            position: absolute;
            width: 60px;
            height: 3px;
            background: #fff;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
        }

        .toggle-panel i.welcome-icon {
            font-size: 60px;
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .toggle-panel p{
            margin-bottom: 20px;
            font-size: 18px;
        }

        .toggle-panel .btn{
            width: 160px;
            height: 46px;
            background: transparent;
            border: 2px solid #fff;
            box-shadow: none;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .toggle-panel .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transition: left 0.5s ease;
            z-index: -1;
        }

        .toggle-panel .btn:hover::before {
            left: 100%;
        }

        .toggle-panel .btn:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        @media screen and (max-width: 650px) {
            .container {
                height: calc(100vh - 40px);
            }

            .form-box{
                bottom: 0;
                width: 100%;
                height: 70%;
            }

            .container.active .form-box{
                right: 0;
                bottom: 30%;
            }

            .toggle-box::before{
                left: 0;
                top: -270%;
                width: 100%;
                height: 300%;
                border-radius: 20vw;
            }

            .container.active .toggle-box::before{
                left: 0;
                top: 70%;
            }

            .toggle-panel{
                width: 100%;
                height: 30%;
            }

            .toggle-panel.toggle-left{
                top: 0;
            }

            .container.active .toggle-panel.toggle-left{
                top: -30%;
                left: 0;
            }

            .toggle-panel.toggle-right{
                right: 0;
                bottom: -30%;
            }

            .container.active .toggle-panel.toggle-right{
                bottom: 0;
            }
        }

        @media screen and (max-width:400px){
            .form-box{
                padding: 20px;
            }

            .toggle-panel h1{
                font-size: 30px;
            }
            
            .toggle-panel i.welcome-icon {
                font-size: 40px;
            }
        }

        /* Added animation for form appearance */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        form {
            animation: fadeIn 0.8s ease-out;
        }

        /* Input field focus animation */
        .input-box::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: #7494ec;
            transition: width 0.4s ease;
        }

        .input-box:focus-within::after {
            width: 100%;
        }
    </style>
</head>
<body>
    
    <div class="container">
        <div class="form-box login">
            <form action="">
                <h1>Login <i class="bx bx-lock-open-alt"></i></h1>
                <div class="input-box">
                    <input type="text" placeholder="Username" required>
                    <i class="bx bxs-user"></i>
                </div>
                <div class="input-box">
                    <input type="password" placeholder="Password" required>
                    <i class="bx bxs-lock-alt"></i>
                </div>
                <div class="forgot-link">
                    <a href="#"><i class="bx bx-question-mark"></i>Forgot password?</a>
                </div>
                <button type="submit" class="btn">Login <i class="bx bx-right-arrow-alt"></i></button>
                <p>or Login with Social Platforms</p>
                <div class="social-icons">
                    <a href="#"><i class="bx bxl-google"></i></a>
                    <a href="#"><i class="bx bxl-facebook"></i></a>
                    <a href="#"><i class="bx bxl-github"></i></a>
                    <a href="#"><i class="bx bxl-linkedin"></i></a>
                    <a href="#"><i class="bx bxl-twitter"></i></a>
                </div>
            </form>
        </div>

        <div class="form-box register">
            <form action="">
                <h1>Registration <i class="bx bx-user-plus"></i></h1>
                <div class="input-box">
                    <input type="text" placeholder="Username" required>
                    <i class="bx bxs-user"></i>
                </div>
                <div class="input-box">
                    <input type="email" placeholder="Email" required>
                    <i class="bx bxs-envelope"></i>
                </div>
                <div class="input-box">
                    <input type="password" placeholder="Password" required>
                    <i class="bx bxs-lock-alt"></i>
                </div>
                <div class="input-box">
                    <input type="tel" placeholder="Phone (optional)">
                    <i class="bx bxs-phone"></i>
                </div>
                <button type="submit" class="btn">Register <i class="bx bx-check"></i></button>
                <p>or Register with Social Platforms</p>
                <div class="social-icons">
                    <a href="#"><i class="bx bxl-google"></i></a>
                    <a href="#"><i class="bx bxl-facebook"></i></a>
                    <a href="#"><i class="bx bxl-github"></i></a>
                    <a href="#"><i class="bx bxl-linkedin"></i></a>
                    <a href="#"><i class="bx bxl-twitter"></i></a>
                </div>
            </form>
        </div>

        <div class="toggle-box">
            <div class="toggle-panel toggle-left">
                <i class="bx bx-user-circle welcome-icon"></i>
                <h1>Hello, Welcome!</h1>
                <p>Don't have an Account? <i class="bx bx-wink-smile"></i></p>
                <button class="btn register-btn">Register <i class="bx bx-right-arrow-alt"></i></button>
            </div>
            <div class="toggle-panel toggle-right">
                <i class="bx bx-shield-quarter welcome-icon"></i>
                <h1>Welcome Back!</h1>
                <p>Already have an Account? <i class="bx bx-happy-heart-eyes"></i></p>
                <button class="btn login-btn">Login <i class="bx bx-right-arrow-alt"></i></button>
            </div>
        </div>
    </div>
    
    <script>
        const container = document.querySelector('.container');
        const registerbtn = document.querySelector('.register-btn');
        const loginbtn = document.querySelector('.login-btn');

        registerbtn.addEventListener('click', ()=>{
            container.classList.add('active');
        });

        loginbtn.addEventListener('click', ()=>{
            container.classList.remove('active');
        });

        // Add input field animation
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('focus', () => {
                input.parentElement.classList.add('focus');
            });
            input.addEventListener('blur', () => {
                if (input.value === '') {
                    input.parentElement.classList.remove('focus');
                }
            });
        });

        // Add button hover effect
        const buttons = document.querySelectorAll('.btn');
        buttons.forEach(button => {
            button.addEventListener('mouseover', () => {
                button.style.transform = 'translateY(-2px)';
            });
            button.addEventListener('mouseout', () => {
                button.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>