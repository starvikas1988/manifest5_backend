<?php

namespace App\Http\Controllers;

use App\Models\MatchModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class MatchModelController extends Controller
{

    public function getAccessToken()
    {
        $cacheKey = 'vendor_api_token';

        // Check if token exists in cache
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // API URL and Credentials
        $authApiUrl = 'https://api.maniifest5.com/api/Auth/token';
        $credentials = [
            'email' => 'john@example.com', // Replace with actual email
            'password' => 'password123',  // Replace with actual password
        ];

        // Make API Request
        $response = Http::post($authApiUrl, $credentials);

        // Check if response is successful
        if ($response->successful()) {
            $data = $response->json();
            $accessToken = $data['response']['accessToken'] ?? null;
            $expiresAt = strtotime($data['response']['expiresAt'] ?? 'now');

            if ($accessToken) {
                // Store token in cache with expiration time (minus a buffer)
                Cache::put($cacheKey, $accessToken, $expiresAt - time() - 60);
                return $accessToken;
            }
        }

        return null; // Return null if authentication fails
    }

    public function fetchMatchesFromVendor()
    {
        $token = $this->getAccessToken(); // Call the method

        if (!$token) {
            return response()->json(['error' => 'Authentication failed'], 401);
        }

        $vendorApiUrl = 'https://api.maniifest5.com/api/Dashboard';
        $bearerToken = $token; // Replace with your actual token
        $perPage = 18; // Define how many items per page
        $currentPage = 1;
        $allMatches = [];

        do {
            // Call API with pagination parameters
            $response = Http::withHeaders([
                'Authorization' => "Bearer $bearerToken",
                'Accept' => 'application/json',
            ])->get($vendorApiUrl, [
                'SeriesId' => 0,
                'StartDate' => "null",
                'SeriesName' => "null",
                'Tab' => 'Live',
                'Page' => $currentPage,
                'PerPage' => $perPage,
            ]);

            // Decode JSON response
            $data = $response->json();
            //dd($data);
            $matches = $data['response']['items'] ?? [];
            $totalPages = $data['response']['totalPages'] ?? 1;

            // Store in array
            $allMatches = array_merge($allMatches, $matches);

            // Move to next page
            $currentPage++;
        } while ($currentPage <= $totalPages); // Continue fetching until last page
       // dd($allMatches);
        // Store data in the matches table
        foreach ($allMatches as $match) {
            MatchModel::updateOrCreate(
                ['m5MatchId' => $match['m5MatchId']], // Unique match identifier
                [
                'm5SeriesId' => $match['m5SeriesId'],
               'm5SeriesName' => $match['m5SeriesName'] ?? null,
                'm5Year' => $match['m5Year'],
                'm5TeamA' => $match['m5TeamA'],
                'm5TeamB' => $match['m5TeamB'],
                'm5MatchNo' => $match['m5MatchNo'],
                'm5StartDate' => isset($match['m5StartDate']) ? date('Y-m-d H:i:s', strtotime($match['m5StartDate'])) : null,
                'm5EndDate' => isset($match['m5EndDate']) ? date('Y-m-d H:i:s', strtotime($match['m5EndDate'])) : null,
                'm5MatchStatusId' =>  $match['m5MatchStatusId'],

                'm5MatchStatus' => $match['m5MatchStatus'],
                'm5MatchFormat' => $match['m5MatchFormat'],
                'm5MatchResult' => $match['m5MatchResult'] ?? null,
                'm5OneBattingTeamName' => $match['m5OneBattingTeamName'] ?? null,
                'm5OneScoresFull' => $match['m5OneScoresFull'] ?? null,
                'm5TwoBattingTeamName' => $match['m5TwoBattingTeamName'] ?? null,
                'm5TwoScoresFull' => $match['m5TwoScoresFull'] ?? null,
                'm5GroundName' => $match['m5GroundName'],
                'm5TeamAShortName' => $match['m5TeamAShortName'] ?? null,
                'm5TeamBShortName' => $match['m5TeamBShortName'] ?? null,
                'm5TeamALogo' => $match['m5TeamALogo'] ?? null,
                'm5TeamBLogo' => $match['m5TeamBLogo'] ?? null,
                 
                'm5Commentary' => $match['m5Commentary'],
                'm5CompetitionName' => $match['m5CompetitionName'],
                'm5CompetitionId' => $match['m5CompetitionId'],
                'm5GroundId' => $match['m5GroundId'],

                'm5MatchStartTimeGMT' => isset($match['m5MatchStartTimeGMT']) ? date('H:i:s', strtotime($match['m5MatchStartTimeGMT'])) : null,
                'm5MatchStartTimeDubai' => isset($match['m5MatchStartTimeDubai']) ? date('H:i:s', strtotime($match['m5MatchStartTimeDubai'])) : null,
                'm5MatchStartTimeLocal' => isset($match['m5MatchStartTimeLocal']) ? date('H:i:s', strtotime($match['m5MatchStartTimeLocal'])) : null,
                'isActive' => $match['isActive'],
                'm5SeriesType' =>  $match['m5SeriesType'],
                'm5GenderName' =>  $match['m5GenderName'],
                ]
            );
           
        }
       

        return response()->json([
            'message' => 'Matches successfully fetched and stored',
            'totalMatches' => count($allMatches)
        ]);
    }

    public function getMatches(Request $request)
    {
       
        // Build query
        $matches  = MatchModel::all();

      
      //  dd($matches);
        // Format response
        $formattedMatches = $matches->map(function ($match) {
            return [
                "m5MatchId" => $match->m5MatchId,
                "m5SeriesId" => $match->m5SeriesId,
                "m5SeriesName" => $match->m5SeriesName,
                "m5Year" => $match->m5Year,
                "m5TeamA" => $match->m5TeamA,
                "m5TeamB" => $match->m5TeamB,
                "m5MatchNo" => $match->m5MatchNo,
                "m5StartDate" => $match->m5StartDate,
                "m5EndDate" => $match->m5EndDate,
                "m5MatchStatusId" => $match->m5MatchStatusId,
                "m5MatchStatus" => $match->m5MatchStatus,
                "m5MatchFormat" => $match->m5MatchFormat,
                "m5MatchResult" => $match->m5MatchResult,

                "m5OneBattingTeamName" => $match->m5OneBattingTeamName,
                "m5OneScoresFull" => $match->m5OneScoresFull,
                "m5TwoBattingTeamName" => $match->m5TwoBattingTeamName,
                "m5TwoScoresFull" => $match->m5TwoScoresFull,
                "m5Commentary" => $match->m5Commentary,
                "m5CompetitionName" => $match->m5CompetitionName,
                "m5CompetitionId" => $match->m5CompetitionId,
                "m5GroundId" => $match->m5GroundId,

                "m5GroundName" => $match->m5GroundName,
                "m5GenderId" => $match->gender_id,
                "m5GenderName" => $match->m5GenderName,
                "m5TeamAShortName" => $match->m5TeamAShortName,
                "m5TeamBShortName" => $match->m5TeamBShortName,
                "m5TeamALogo" => $match->m5TeamALogo,
                "m5TeamBLogo" => $match->m5TeamBLogo,
                "m5MatchStartTimeGMT" => $match->m5MatchStartTimeGMT,
                "m5MatchStartTimeDubai" => $match->m5MatchStartTimeDubai,
                "m5MatchStartTimeLocal" => $match->m5MatchStartTimeLocal,
                "isActive" => $match->isActive,
                "m5SeriesType" => $match->m5SeriesType,
            ];
        });

        return response()->json([
            "status" => "ok",
            "isSuccess" => true,
            "response" => [
                "items" => $formattedMatches,
                "totalItems" => $matches->count(),
                "totalPages" => 1
            ]
        ]);
    }

    public function fetchMatchesById($matchId)
    {
        try {
            // Fetch match details from vendor_matches table
            $match = DB::table('vendor_matches')->where('m5MatchId', $matchId)->first();
    
            // Check if match exists
            if (!$match) {
                return response()->json([
                    'success' => false,
                    'message' => 'Match not found',
                ], 404);
            }
    
            return response()->json([
                'success' => true,
                'message' => 'Match details fetched successfully',
                'data' => $match,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }
    

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
