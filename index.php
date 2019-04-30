<?php
/**----------------------------------------------------------------------------------
 * Microsoft Developer & Platform Evangelism
 *
 * Copyright (c) Microsoft Corporation. All rights reserved.
 *
 * THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF ANY KIND,
 * EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE IMPLIED WARRANTIES
 * OF MERCHANTABILITY AND/OR FITNESS FOR A PARTICULAR PURPOSE.
 *----------------------------------------------------------------------------------
 * The example companies, organizations, products, domain names,
 * e-mail addresses, logos, people, places, and events depicted
 * herein are fictitious.  No association with any real company,
 * organization, product, domain name, email address, logo, person,
 * places, or events is intended or should be inferred.
 *----------------------------------------------------------------------------------
 **/

/** -------------------------------------------------------------
# Azure Storage Blob Sample - Demonstrate how to use the Blob Storage service.
# Blob storage stores unstructured data such as text, binary data, documents or media files.
# Blobs can be accessed from anywhere in the world via HTTP or HTTPS.
#
# Documentation References:
#  - Associated Article - https://docs.microsoft.com/en-us/azure/storage/blobs/storage-quickstart-blobs-php
#  - What is a Storage Account - http://azure.microsoft.com/en-us/documentation/articles/storage-whatis-account/
#  - Getting Started with Blobs - https://azure.microsoft.com/en-us/documentation/articles/storage-php-how-to-use-blobs/
#  - Blob Service Concepts - http://msdn.microsoft.com/en-us/library/dd179376.aspx
#  - Blob Service REST API - http://msdn.microsoft.com/en-us/library/dd135733.aspx
#  - Blob Service PHP API - https://github.com/Azure/azure-storage-php
#  - Storage Emulator - http://azure.microsoft.com/en-us/documentation/articles/storage-use-emulator/
#
 **/
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'vendor/autoload.php';

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;

$connectionString = "DefaultEndpointsProtocol=https;AccountName=chonggwebapp;AccountKey=vtAzI9IyDHjhwMJIroZBd8OOjWP8WmjQO9J9JNOqK5RGA8ZbVlRu+Wn3ZLDjWp20Q6oVqBXEMhlVmkwbJht3Ow==;EndpointSuffix=core.windows.net";

// Create blob client.
$blobClient = BlobRestProxy::createBlobService($connectionString);

$containerName = "blobchongieball";

if (isset($_POST['upload'])) {
    $nameFile = $_FILES['fileToUpload']['name'];
    $uploadFile = fopen($_FILES["fileToUpload"]["tmp_name"], "r");
    $blobClient->createBlockBlob($containerName, $nameFile, $uploadFile);
}

if (isset($_POST['analyze'])) {
    $url = $_POST['url_blob'];
}

$listBlobsOptions = new ListBlobsOptions();
$listBlobsOptions->setPrefix("");
$result = $blobClient->listBlobs($containerName, $listBlobsOptions);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Dicoding Submission 2</title>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
</head>
<body>
    <h2>Please Upload File</h2>
    <form action="" method="post" enctype="multipart/form-data">
        <input type="file" name="fileToUpload" accept=".jpeg,.jpg,.png" required="">
        <input type="submit" name="upload">
    </form>
    <?php
        echo "These are the blobs present in the container: <br/>";

        do{
            $result = $blobClient->listBlobs($containerName, $listBlobsOptions);
            foreach ($result->getBlobs() as $blob)
            {
    ?>
                <form action="" method="post">
                    <input type="text" name="name_blob" readonly value="<?php echo $blob->getName()?>">
                    <input type="text" name="url_blob" readonly value="<?php echo $blob->getUrl()?>">
                    <button type="submit" name="analyze">Analyze</button>
                </form>
    <?php
            }

            $listBlobsOptions->setContinuationToken($result->getContinuationToken());
        } while($result->getContinuationToken());
        echo "<br />";
    ?>

    <div id="wrapper" style="width:1020px; display:table;" hidden>
        <div id="jsonOutput" style="width:600px; display:table-cell;">
            Response:
            <br><br>
            <textarea id="responseTextArea" class="UIInput"
                      style="width:580px; height:400px;"></textarea>
        </div>
        <div id="imageDiv" style="width:420px; display:table-cell;">
            Source image:
            <br><br>
            <img id="sourceImage" width="400" />
            <br>
            <span id="description"></span>
        </div>
    </div>
</body>
<script type="text/javascript">
    var isAnalyze = "<?php echo isset($url);?>";

    if (isAnalyze) {
        processImage();
    }

    function processImage() {
        // **********************************************
        // *** Update or verify the following values. ***
        // **********************************************

        // Replace <Subscription Key> with your valid subscription key.
        var subscriptionKey = "d9ee8c5f4a27485ebb7f9aa3ebeac08c";

        // You must use the same Azure region in your REST API method as you used to
        // get your subscription keys. For example, if you got your subscription keys
        // from the West US region, replace "westcentralus" in the URL
        // below with "westus".
        //
        // Free trial subscription keys are generated in the "westus" region.
        // If you use a free trial subscription key, you shouldn't need to change
        // this region.
        var uriBase =
            "https://southeastasia.api.cognitive.microsoft.com/vision/v2.0/analyze";

        // Request parameters.
        var params = {
            "visualFeatures": "Categories,Description,Color",
            "details": "",
            "language": "en",
        };

        // Display the image.
        var sourceImageUrl = "<?php echo $url;?>";
        document.querySelector("#sourceImage").src = sourceImageUrl;

        // Make the REST API call.
        $.ajax({
            url: uriBase + "?" + $.param(params),

            // Request headers.
            beforeSend: function(xhrObj){
                xhrObj.setRequestHeader("Content-Type","application/json");
                xhrObj.setRequestHeader(
                    "Ocp-Apim-Subscription-Key", subscriptionKey);
            },

            type: "POST",

            // Request body.
            data: '{"url": ' + '"' + sourceImageUrl + '"}',
        })

            .done(function(data) {
                // Show formatted JSON on webpage.
                $("#responseTextArea").val(JSON.stringify(data, null, 2));
                $("#description").text(data.description.captions[0].text);
            })

            .fail(function(jqXHR, textStatus, errorThrown) {
                // Display error message.
                var errorString = (errorThrown === "") ? "Error. " :
                    errorThrown + " (" + jqXHR.status + "): ";
                errorString += (jqXHR.responseText === "") ? "" :
                    jQuery.parseJSON(jqXHR.responseText).message;
                alert(errorString);
            });
    };
</script>
</html>
