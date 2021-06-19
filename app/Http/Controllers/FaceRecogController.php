<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Aws\Rekognition\RekognitionClient;
use Aws\Sts\StsClient;

class FaceRecogController extends Controller
{
    public function showForm()
    {
        return view('form');
    }
    public function submitForm(Request $request)
    {
        // $stsClient = new StsClient([
        //     'profile' => 'default',
        //     'region' => 'us-east-2',
        //     'version' => '2011-06-15'
        // ]);
        // $sessionToken = $stsClient->getSessionToken();
        // dd($stsClient);
        $client = new RekognitionClient([
            'profile'   => 'project1',
            'region'    => env('AWS_DEFAULT_REGION'),
            'version'   => 'latest'
        ]);

        $image = fopen($request->file('photo')->getPathName(), 'r');
        $bytes = fread($image, $request->file('photo')->getSize());

        if ($request->input('type') === 'faces') {
            // $results = $client->detectModerationLabels(['Image' => ['Bytes' => $bytes], 'MinConfidence' => intval($request->input('confidence'))])['ModerationLabels'];
            $results = $client->DetectFaces(
                array(
                'Image' => array(
                   'Bytes' => $bytes,
                ),
                'Attributes' => array('ALL')
                )
            );
          
            $faceDetails = $results['FaceDetails'];
          
            if (empty($faceDetails)) {
                return "NO Face Detected";
            } else {
                $numOfFaces = count($faceDetails);

                return "One or more Face Detected \n Number of Faces Detected:" . $numOfFaces;
            }
        } else {
            $results = $client->detectText(['Image' => ['Bytes' => $bytes], 'MinConfidence' => intval($request->input('confidence'))])['TextDetections'];
    
            $string = '';
            foreach ($results as $item) {
                if ($item['Type'] === 'WORD') {
                    $string .= $item['DetectedText'] . ' ';
                }
            }
    
            if (empty($string)) {
                $message = 'This photo does not have any words';
            } else {
                $message = 'This photo says ' . $string;
            }
        }
    
        request()->session()->flash('success', $message);
    
        return view('form', ['results' => $results]);
    }
}
