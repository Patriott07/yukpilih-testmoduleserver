<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\poll;
use App\Models\choice;
use App\Models\vote;
use Carbon\Carbon;
use Mockery\Undefined;
use Illuminate\Support\Facades\DB;

use function PHPSTORM_META\map;

class PollController extends Controller
{
    public function post(Request $request)
    {
        $validate = $request->validate([
            'title' => 'required',
            'description' => 'required',
            'deadline' => 'required',
            'choices' => 'required|array|min:2',
        ]);

        // dd($request->all());
        $authUser = Auth::user();
        $request['created_by'] = $authUser->id;

        $Poll = poll::create($request->all());
        if ($Poll) {
            $id_poll = $Poll->id;

            foreach ($request->choices as $response) {
                $data = [
                    'polls_id' => $id_poll,
                    'choice' => $response,
                    'created_at' => Carbon::now()
                ];

                choice::create($data);
            }

            return response()->json(['message' => 'Success Make a poll', 'data' => $Poll]);
        }

        return response()->json(['message' => 'the given data was invalid'], 422);

        // dd($request->all());


    }

    public function getAll()
    {
            $poll = poll::orderByDesc('created_at')->get();
            $datavote = [];

            foreach ($poll as $response) {
               
                $vote = vote::with(['choice'])->where('poll_id', $response->id)->get();
                $totalVote = count($vote);
                
                $calc = ($totalVote > 0) ? 1 / $totalVote : 0; 
                $datavote[] = [
                    'title' => $response->title,
                    'total_votes' => $totalVote
                   
                ];

                $vote_items = [];
                

                foreach($vote as $voteas){
                    if(array_key_exists($voteas->choice_id, $vote_items)){
                        $vote_items[$voteas->choice_id] =  $vote_items[$voteas->choice_id] + 1;
                    }else{
                        $vote_items[$voteas->choice_id] = 1; 
                    }
                }

                dd($vote_items,$totalVote,$vote->all());

                dd($vote->all());

                dd($datavote);
            }

            // dd($datavote);
            
            // return $datavote;

            // $array = array_map(function($response){
            //     return $response;
            // }, $datavote);

            // dd($array);

            // foreach ($datavote as $data) {
            //     // goal : 100%
            //     // votes total 5
            //     // votes a : 4
            //     // votes b : 1
            //     // cara hitung
            //     // $number1 = total votes / vote(A)
            //     // $pointA = 100% * $number1
            //     // var_dump($data);
            //     // var_dump($data);
            //     // dd($data);
            // }
            // return response()->json(['data' => $poll]);
        // } else {
        //     dd('user');
        // }
    }

    public function getPollById($id){
        //get spesifik poll dari database
    }

    public function vote(Request $request,$poll_id, $choice_id){
        $authUser = Auth::user();
        // dd($authUser);
     
        if(! choice::where('id',$choice_id)->exists() || ! poll::where('id',$poll_id)->exists()){
            return response()->json(['message' => 'Invalid Choices'],422);
        }

        $poll = poll::findOrFail($poll_id);
        
        
        // $vote = vote::where('user_id', $authUser->id)->where('poll_id', $poll_id)->get();
        if(vote::where('user_id', $authUser->id)->where('poll_id', $poll_id)->exists()){
            return response()->json(['message' => 'You has choice for this poll'],422);
        }

        if(Carbon::now()->gte($poll->deadline)){
            return response()->json(['message' => 'polling sudah kadaluarsa'], 422);
        }

        $post = vote::create([
            'choice_id' => $choice_id,
            'user_id' => $authUser->id,
            'poll_id' => $poll_id,
            'division_id' => $authUser->divisions_id,
        ]);

        if($post){
            return response()->json(['message' => 'Success Voting'], 200);
        }


    }

    public function delete($poll_id){
        $delete = poll::findOrFail($poll_id)->delete();
        if($delete){
            return response()->json(['message' => 'Deleted!'], 200);
        }
    }
}
