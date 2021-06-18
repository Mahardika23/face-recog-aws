<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Aws\Rekognition\RekognitionClient;

class FaceRecogController extends Controller
{
    public function showForm()
    {
        return view('form');
    }
    public function submitForm(Request $request)
    {
        $client = new RekognitionClient([
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
            // if (array_search('Explicit Nudity', array_column($results, 'Name'))) {
            //     $message = 'This photo may contain nudity';
            // } else {
            //     $message = 'This photo does not contain nudity';
            // }

            $faceDetails = $results['FaceDetails'];
            //         print 'People: Image position and estimated age' . PHP_EOL;
            //         for ($n=0;$n<sizeof($results['FaceDetails']); $n++) {
            //             print 'Position: ' . $results['FaceDetails'][$n]['BoundingBox']['Left'] . " "
            //   . $results['FaceDetails'][$n]['BoundingBox']['Top']
            //   . PHP_EOL
            //   . 'Age (low): '.$results['FaceDetails'][$n]['AgeRange']['Low']
            //   .  PHP_EOL
            //   . 'Age (high): ' . $results['FaceDetails'][$n]['AgeRange']['High']
            //   .  PHP_EOL . PHP_EOL;
            // }

            if (empty($faceDetails)) {
                return "NO Face Detected";
            } else {
                $numOfFaces = count($faceDetails);

                return "Number of Faces Detected:" . $numOfFaces;
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
