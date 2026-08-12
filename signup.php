<?php
session_start();
include('config.php');

// which role are we signing up as
$role = "designer";
if (isset($_GET['role']) && $_GET['role'] == "model") {
    $role = "model";
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name  = mysqli_real_escape_string($conn, $_POST['last_name']);
    $phone      = mysqli_real_escape_string($conn, $_POST['phone']);
    $email      = mysqli_real_escape_string($conn, $_POST['email']);
    $password   = $_POST['password'];
    $confirm    = $_POST['confirm_password'];
    $full_name  = $first_name . " " . $last_name;

    if ($first_name == "" || $last_name == "" || $email == "" || $password == "") {
        $error = "Please fill all required fields.";
    } elseif ($password != $confirm) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {

        // pick the right table depending on role
        if ($role == "model") {
            $check = mysqli_query($conn, "SELECT * FROM model WHERE email = '$email'");
        } else {
            $check = mysqli_query($conn, "SELECT * FROM designer WHERE email = '$email'");
        }

        if (mysqli_num_rows($check) > 0) {
            $error = "An account with this email already exists.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            if ($role == "model") {
                $gender = mysqli_real_escape_string($conn, $_POST['gender']);
                $age = $_POST['age'];
                if ($age == "") { $age = 0; }

                $sql = "INSERT INTO model (model_name, email, password, phone, gender, age)
                        VALUES ('$full_name', '$email', '$hashed_password', '$phone', '$gender', '$age')";
                mysqli_query($conn, $sql);
                $_SESSION['user_id'] = mysqli_insert_id($conn);
                $_SESSION['role'] = "model";
                $_SESSION['name'] = $full_name;
                header("Location: model/dashboard.php");
                exit;

            } else {
                $experience = mysqli_real_escape_string($conn, $_POST['experience']);

                $sql = "INSERT INTO designer (designer_name, email, password, phone, experience)
                        VALUES ('$full_name', '$email', '$hashed_password', '$phone', '$experience')";
                mysqli_query($conn, $sql);
                $_SESSION['user_id'] = mysqli_insert_id($conn);
                $_SESSION['role'] = "designer";
                $_SESSION['name'] = $full_name;
                header("Location: designer/dashboard.php");
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Sign Up - CastFlow</title>
<link rel="stylesheet" href="css/style.css">
<style>
        .right{
    width:700px;
}

.top{
    width:620px;
    height:350px;
    object-fit:cover;
    margin-bottom:20px;
}

.bottom{
    display:flex;
    gap:20px;
}

.bottom img{
    width:300px;
    height:420px;
    object-fit:cover;
}
</style>
</head>
<body>
<div class="auth-shell">
    <div class="brand-mark">CASTFLOW</div>
    <div class="auth-split">
        <div class="auth-form-side">
            <h1>Welcome!</h1>
            <h2>Create an account</h2>

            <div class="role-pill-toggle">
                <a href="signup.php?role=designer" class="<?php if ($role == 'designer') echo 'active'; ?>">Designer</a>
                <a href="signup.php?role=model" class="<?php if ($role == 'model') echo 'active'; ?>">Model</a>
            </div>

            <p class="auth-switch">Already have an account? <a href="login.php?role=<?php echo $role; ?>">Log in</a></p>

            <form class="auth-form" method="POST" action="signup.php?role=<?php echo $role; ?>">

                <?php if ($error != "") { ?>
                    <div class="error-box full"><?php echo $error; ?></div>
                <?php } ?>

                <div class="field"><label>First Name</label>
                    <input type="text" name="first_name" placeholder="First Name" required></div>
                <div class="field"><label>Last Name</label>
                    <input type="text" name="last_name" placeholder="Last Name" required></div>

                <div class="field"><label>Phone</label>
                    <input type="text" name="phone" placeholder="Phone"></div>
                <div class="field"><label>Email</label>
                    <input type="email" name="email" placeholder="Email" required></div>

                <?php if ($role == "model") { ?>
                    <div class="field"><label>Gender</label>
                        <select name="gender">
                            <option value="">Select</option>
                            <option value="Female">Female</option>
                            <option value="Male">Male</option>
                            <option value="Non-binary">Non-binary</option>
                        </select>
                    </div>
                    <div class="field"><label>Age</label>
                        <input type="number" name="age" placeholder="Age"></div>
                <?php } else { ?>
                    <div class="field full"><label>Experience</label>
                        <input type="text" name="experience" placeholder="e.g. 3 years"></div>
                <?php } ?>

                <div class="field"><label>Password</label>
                    <input type="password" name="password" placeholder="Password" required></div>
                <div class="field"><label>Confirm Password</label>
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required></div>

                <button type="submit" class="btn-submit">Sign Up</button>
            </form>
        </div>
        



            <div class="right">

        <img src="top.jpg" class="top">

        <div class="bottom">
            <img src="left.jpg">
            <img src="right.jpg">
        </div>

    </div>


        <!-- <div class="auth-visual" style="background-image:url('https://images.unsplash.com/photo-1445205170230-053b83016050?q=80&w=1200');"></div> -->



    </div>
</div>
</body>
</html>
