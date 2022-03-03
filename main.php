<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") 
{
    $values = parse_url($_REQUEST["url"]);
    $path = explode('/', $values["path"]);
    $key = $path[3];
    echo $key;
}
else{
?>
<html>
    <form class="form" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
    link del foglio:<input id="ins-link" type="text" name="url" required>
    <button type="submit" class="mandalink">manda link</button>
    </form>
</html>
<?php
}
?>
