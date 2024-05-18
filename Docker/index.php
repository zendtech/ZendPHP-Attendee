<?php
echo '<a href="http://orderapp/">OrderApp</a>';
echo '<a href="http://sandbox/">Sandbox</a>';
echo '<a href="http://php-examples/">PHP Examples</a>';
echo '<a href="http://dbadmin/">DB Admin</a>';
echo <<<EOT
To test PHP code examples proceed as follows:<br />
<ol>
<li>Create your PHP code file in your code editor on your own computer</li>
<li>Save the file as "/path/to/course/files/sandbox/public/NAME_OF_FILE.php" (change "NAME_OF_FILE" to an appropriate name)</li>
<li>From the browser on your own computer open this URL: "http://sandbox/NAME_OF_FILE.php"</li>
</ol>
EOT;
phpinfo();
