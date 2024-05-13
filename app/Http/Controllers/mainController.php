<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class mainController extends Controller
{
    private $errorMessage = "";
    public function main()
    {
        //session()->flush();
        if (!session()->has("savedResults./api")) {
            $result = $this->apiRequest("/api");
            if ($result) {
                session()->put("savedResults./api", json_decode($result));
            }
        }
        if (!session()->has("decisionTree")) {
            session()->put("decisionTree", ["/api"]);
        }
        return view("mainpage");
    }

    public function newResultRequest(Request $request)
    {
        $selectedResult = $request->input("selectedResult");
        if ($selectedResult == null) {
            return redirect()->back()->with("error", "unexpected key");
        } else if ($selectedResult == "home") {
            $decisionTree = session()->get("decisionTree");
            session()->put("decisionTree", array_slice($decisionTree, 0, 1));
            $selectedResult = "/api";
        } else if ($selectedResult == "previous") {
            $decisionTree = session()->get("decisionTree");
            $decisionCount = count($decisionTree);
            if ($decisionCount > 1) {
                $selectedResult = $decisionTree[$decisionCount - 2];
                session()->put("decisionTree", array_slice($decisionTree, 0, $decisionCount - 1));
            }
        } else {
            if (!session()->has("savedResults." . $selectedResult)) {
                $newResult = $this->apiRequest($selectedResult);
                if (!$newResult) {
                    return redirect()->back()->with("error", $this->errorMessage);
                }
                session()->put("savedResults." . $selectedResult, json_decode($newResult));
            }
            $decisionTree = session()->get("decisionTree");
            $decisionCount = count($decisionTree);
            if($decisionTree[$decisionCount-1] != $selectedResult){
                session()->push("decisionTree", $selectedResult);
            }
        }
        return redirect()->route("mainpage");
    }

    private function apiRequest($slug): string | false
    {
        $url = 'https://www.dnd5eapi.co' . $slug;
        $apiData = Http::timeout(5)->get($url);
        if ($apiData->successful()) {
            return $apiData->body();
        } else if ($apiData->requestTimeout()) {
            $this->errorMessage = "failed to connect to the server";
            return false;
        } else {
            $this->errorMessage = " does not exist";
            return false;
        }
    }
}
