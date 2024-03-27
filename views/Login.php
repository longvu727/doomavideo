<?php include_once("views/HeaderINC.php");?>
        <div class="loginForm">  
            <!--<a class="loginClose">x</a>  -->
            <h1>Login</h1>
            <span class="errorMessage"><?php print $VIEW_VARS['loginError']; ?></span>
            <form action="?action=login" method="post">  
                <label class="loginLabels">Username:</label>
                <input class="loginInputs" type="text" name="username"/><span>
                <label class="loginLabels">Password:</label>
                <input class="loginInputs" type="password" name="password"/>
                <input type="submit" name="submit" value="Login"/>
            </form>
        </div>  
<?php include_once("views/FooterINC.php");?>  