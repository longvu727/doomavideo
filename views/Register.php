<?php include_once("views/HeaderINC.php");?>
        <div class="registerForm">  
            <!--<a class="registerClose">x</a>  -->
            <h1>Register</h1>
            <form action="?action=register" method="post" id="registerForm">  
                <label class="registerLabels">Username:</label>
                <input class="registerInputs" type="text" name="username"/><span>
                    <br/>
                
                <label class="registerLabels">Password:</label>
                <input class="registerInputs" type="password" id="password" name="password"/>
                <br/>
                
                <label class="registerLabels">Retype Password:</label>
                <input class="registerInputs" type="password" id="retypepassword" name="retypepassword"/>
                <span id="errorMessage" class="errorMessage"><label class="registerLabels">&nbsp;</label>password does not match</span>
                <br/>
                
                <label class="registerLabels">Birthday:</label>
                <select name="birthmonth">
                    <option value="0">Month</option>
                    <option value="1">Jan</option>
                    <option value="2">Feb</option>
                    <option value="3">Mar</option>
                    <option value="4">Apr</option>
                    <option value="5">May</option>
                    <option value="6">Jun</option>
                    <option value="7">Jul</option>
                    <option value="8">Aug</option>
                    <option value="9">Sep</option>
                    <option value="10">Oct</option>
                    <option value="11">Nov</option>
                    <option value="12">Dec</option>
                    </select>
                <select name="birthday">
                    <option value="0">Day</option>
                    <?php  for( $i=1; $i<32; $i++) { print "<option value=\"$i\">$i</option>"; } ?>
                </select>
                <select name="birthyear">
                    <option value="0">Year</option>
                    <?php  for( $i=2011; $i>1899; $i--) { print "<option value=\"$i\">$i</option>"; } ?>
                </select>
                <br/>
                
                <label class="registerLabels">Gender:</label>
                <input type="radio" name="gender" value="male" CHECKED/> Male
                <input type="radio" name="gender" value="female" /> Female
                
                <?php print $VIEW_VARS['captchaHtml'];?>
                <input type="submit" name="submit" value="Register"/>
            </form>
        </div>  
        <script type="text/javascript">
            function retypePasswordCheck() {
                if( $('#password').val() == $('#retypepassword').val() ) { 
                    return true; 
                }
                return false;
            } 
            $('#retypepassword').keyup( function (){
                if( retypePasswordCheck() ) { $('#errorMessage').css('display','none'); }
                else { $('#errorMessage').css('display','block'); }
            });
            $('#registerForm').submit( function() {
                if( retypePasswordCheck() ) { 
                    return true; 
                }
                return false;
            });
        </script>
<?php include_once("views/FooterINC.php");?>  