
<?php
function ListFolder($path)
{
    //using the opendir function
    $dir_handle = @opendir($path) or die("Unable to open $path");
    
    //Leave only the lastest folder name
    $dirname = end(explode("/", $path));
    
    //display the target folder.
    //echo ("<li>$dirname\n");


    echo "<ul>\n";
    while (false !== ($file = readdir($dir_handle))) 
    {
        

        if($file!="." && $file!="..")
        {
//	    echo "<li><a href='/".$file."'>$file</li>";

            if (is_dir($path."/".$file))
            {
                //Display a list of sub folders.
                ListFolder($path."/".$file);
            }
            else
            {
                //Display a list of files.
                echo "<li>$file</li>";
            }

        }

    }
    echo "</ul>\n";

    echo "</li>\n";
    
    //closing the directory
    closedir($dir_handle);
}

//use
echo '<h3>Project list</h3>';
 ListFolder($_SERVER['DOCUMENT_ROOT']);
