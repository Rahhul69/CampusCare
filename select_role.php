<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Role - CampusCare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4A3AFF;
            --primary-light: #6659FF;
            --primary-dark: #3D2EE4;
            --secondary: #6c757d;
            --success: #00C896;
            --info: #0dcaf0;
            --light: #f8f9fa;
            --dark: #2c3e50;
            --card-shadow: 0 8px 32px rgba(21, 48, 142, 0.15);
            --light-purple: rgba(74, 58, 255, 0.05);
            --glass-bg: rgba(255, 255, 255, 0.25);
            --glass-border: rgba(255, 255, 255, 0.18);
            --glass-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes floatIcon {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-10px) scale(1.05); }
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(74, 58, 255, 0.6); }
            70% { box-shadow: 0 0 0 15px rgba(74, 58, 255, 0); }
            100% { box-shadow: 0 0 0 0 rgba(74, 58, 255, 0); }
        }

        @keyframes slideDown {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes moveBubble {
            0% { transform: translate(0, 0); }
            33% { transform: translate(30px, -50px); }
            66% { transform: translate(-20px, -70px); }
            100% { transform: translate(0, 0); }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        body {
            padding-top: 90px;
            margin: 0;
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #7678ed 0%, #4a3aff 100%);
            overflow-x: hidden;
            position: relative;
        }

        /* Dynamic Background Elements */
        .dynamic-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.5;
            filter: blur(60px);
        }

        .shape-1 {
            width: 500px;
            height: 500px;
            background: linear-gradient(45deg, #8c9eff, #536dfe);
            top: -150px;
            right: -150px;
            animation: float 15s ease-in-out infinite;
        }

        .shape-2 {
            width: 400px;
            height: 400px;
            background: linear-gradient(45deg, #00c896, #3d5afe);
            bottom: -100px;
            left: -150px;
            animation: float 20s ease-in-out infinite reverse;
        }

        .shape-3 {
            width: 300px;
            height: 300px;
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            top: 40%;
            left: 15%;
            animation: float 18s ease-in-out infinite 2s;
        }

        .bubble {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .bubble-1 {
            width: 100px;
            height: 100px;
            top: 20%;
            right: 20%;
            animation: moveBubble 20s linear infinite;
        }

        .bubble-2 {
            width: 50px;
            height: 50px;
            bottom: 30%;
            left: 25%;
            animation: moveBubble 15s linear infinite 2s;
        }

        .bubble-3 {
            width: 70px;
            height: 70px;
            top: 60%;
            right: 30%;
            animation: moveBubble 25s linear infinite 5s;
        }

        .circle-pattern {
            position: absolute;
            width: 300px;
            height: 300px;
            border: 2px dashed rgba(255, 255, 255, 0.3);
            border-radius: 50%;
        }

        .circle-1 {
            top: 15%;
            left: 10%;
            animation: rotate 40s linear infinite;
        }

        .circle-2 {
            bottom: 20%;
            right: 15%;
            width: 400px;
            height: 400px;
            animation: rotate 60s linear infinite reverse;
        }

        /* Glass Morphism Effect */
        .role-selection {
            text-align: center;
            background: var(--glass-bg);
            padding: 3.5rem;
            border-radius: 24px;
            box-shadow: var(--glass-shadow);
            max-width: 750px;
            width: 90%;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            animation: fadeInUp 0.8s ease-out;
            position: relative;
            z-index: 10;
            border: 1px solid var(--glass-border);
            overflow: hidden;
        }

        .role-selection::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 20%, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            z-index: -1;
        }

        h1 {
            color: white;
            margin-bottom: 1.2rem;
            font-size: 2.7rem;
            font-weight: 700;
            position: relative;
            padding-bottom: 1.2rem;
            letter-spacing: -0.5px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 70px;
            height: 4px;
            background: rgba(255, 255, 255, 0.7);
            border-radius: 2px;
        }

        p {
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 3.2rem;
            font-size: 1.2rem;
            max-width: 80%;
            margin-left: auto;
            margin-right: auto;
            text-shadow: 0 1px 8px rgba(0, 0, 0, 0.1);
        }

        .role-buttons {
            display: flex;
            justify-content: center;
            gap: 3rem;
            margin-bottom: 3rem;
        }

        .role-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2.7rem 2.2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            width: 200px;
            text-decoration: none;
            position: relative;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .role-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
            opacity: 0;
            transition: opacity 0.4s ease;
            z-index: 1;
        }

        .role-btn::after {
            content: '';
            position: absolute;
            bottom: -50px;
            right: -50px;
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            opacity: 0;
            transition: opacity 0.4s ease, transform 0.4s ease;
            z-index: 0;
        }

        .role-btn:hover {
            transform: translateY(-10px);
            border-color: rgba(255, 255, 255, 0.5);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
            background: rgba(255, 255, 255, 0.18);
        }

        .role-btn:hover::before {
            opacity: 1;
        }

        .role-btn:hover::after {
            opacity: 1;
            transform: scale(3);
        }

        .role-btn i {
            font-size: 3.2rem;
            color: white;
            margin-bottom: 1.7rem;
            position: relative;
            z-index: 2;
            animation: floatIcon 5s ease-in-out infinite;
            transition: color 0.3s ease;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .role-btn:hover i {
            color: white;
            transform: scale(1.1);
        }

        .role-btn span {
            color: white;
            font-weight: 600;
            font-size: 1.2rem;
            position: relative;
            z-index: 2;
            transition: color 0.3s ease;
            text-shadow: 0 1px 5px rgba(0, 0, 0, 0.1);
        }

        .role-btn:hover span {
            color: white;
        }

        .admin-link {
            margin-top: 2.8rem;
            padding-top: 1.8rem;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        .admin-link a {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-size: 1rem;
            font-weight: 500;
            padding: 0.9rem 1.8rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.05);
        }

        .admin-link a:hover {
            color: white;
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .admin-link a i {
            font-size: 1.2rem;
            transition: transform 0.3s ease;
        }

        .admin-link a:hover i {
            transform: translateX(3px);
        }

        .header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            padding: 1.5rem 0;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
            z-index: 1000;
            animation: slideDown 0.6s ease-out;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .header-content {
            max-width: 1300px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 1.2rem;
            text-decoration: none;
            position: relative;
        }

        .logo i {
            font-size: 2.5rem;
            color: white;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            animation: pulse 2.5s infinite;
        }

        .logo-text {
            display: flex;
            flex-direction: column;
        }

        .logo-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: white;
            letter-spacing: -0.5px;
            position: relative;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .logo-subtitle {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 500;
            text-shadow: 0 1px 5px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 768px) {
            .role-selection {
                padding: 2.5rem 2rem;
                width: 85%;
            }

            .role-buttons {
                flex-direction: column;
                align-items: center;
                gap: 2rem;
            }

            .role-btn {
                width: 100%;
                max-width: 280px;
                padding: 2.2rem 1.8rem;
            }

            h1 {
                font-size: 2.2rem;
            }

            p {
                font-size: 1.1rem;
                max-width: 100%;
            }

            .header {
                padding: 1rem 0;
            }

            .header-content {
                padding: 0 1.2rem;
            }

            .logo i {
                font-size: 2.2rem;
            }

            .logo-title {
                font-size: 1.4rem;
            }

            .logo-subtitle {
                font-size: 0.8rem;
            }

            body {
                padding-top: 74px;
            }

            .shape-1, .shape-2, .shape-3 {
                transform: scale(0.7);
            }
        }
    </style>
</head>
<body>
    <div class="dynamic-bg">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
        <div class="bubble bubble-1"></div>
        <div class="bubble bubble-2"></div>
        <div class="bubble bubble-3"></div>
        <div class="circle-pattern circle-1"></div>
        <div class="circle-pattern circle-2"></div>
    </div>

    <header class="header">
        <div class="header-content">
            <a href="index.php" class="logo">
                <i class="fas fa-hospital-user"></i>
                <div class="logo-text">
                    <span class="logo-title">CampusCare</span>
                    <span class="logo-subtitle">A Unified College Maintenance System</span>
                </div>
            </a>
        </div>
    </header>
    
    <div class="role-selection">
        <h1>Welcome to CampusCare</h1>
        <p>Please select your role to continue to our campus maintenance portal</p>
        <div class="role-buttons">
            <a href="student_login.php" class="role-btn">
                <i class="fas fa-user-graduate"></i>
                <span>Student</span>
            </a>
            <a href="faculty_login.php" class="role-btn">
                <i class="fas fa-chalkboard-teacher"></i>
                <span>Faculty</span>
            </a>
        </div>
        <div class="admin-link">
            <a href="admin_login.php">
                <i class="fas fa-user-shield"></i>
                Admin Login
            </a>
        </div>
    </div>
</body>
</html>