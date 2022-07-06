<?php

namespace App\Http\Controllers\Admin;

use App\Exports\QuestionsExport;
use App\Http\Controllers\Controller;
use App\Imports\QuestionsImport;
use App\Models\Answer;
use App\Models\Exam;
use App\Models\Questions;
use App\Models\Skill;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class QuestionController extends Controller
{
    protected $skillModel;
    protected $questionModel;
    protected $answerModel;
    protected $examModel;
    public function __construct(Skill $skill, Questions $question, Answer $answer, Exam $exam)
    {
        $this->skillModel = $skill;
        $this->questionModel = $question;
        $this->answerModel = $answer;
        $this->examModel = $exam;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getList()
    {
        try {
            $now = Carbon::now('Asia/Ho_Chi_Minh');
            $data = $this->questionModel::when(request()->has('question_soft_delete'), function ($q) {
                return $q->onlyTrashed();
            })
                ->status(request('status'))
                ->search(request('q') ?? null, ['content'])
                ->sort((request('sort') == 'asc' ? 'asc' : 'desc'), request('sort_by') ?? null, 'questions')
                ->whenWhereHasRelationship(request('skill') ?? null, 'skills', 'skills.id')
                // ->hasRequest(['rank' => request('level') ?? null, 'type' => request('type') ?? null]);
                ->when(request()->has('level'), function ($q) {
                    $q->where('rank', request('level'));
                })
                ->when(request()->has('type'), function ($q) {
                    $q->where('type', request('type'));
                });
            $data->with(['skills', 'answers']);
            return $data;
        } catch (\Throwable $th) {
            dd($th);
        }
    }
    public function index()
    {
        $skills = $this->skillModel::all();
        if (!($questions = $this->getList()->paginate(request('limit') ?? 5))) return abort(404);

        // dd($questions);
        return view('pages.question.list', [
            'questions' => $questions,
            'skills' => $skills,
        ]);
    }

    public function indexApi()
    {
        try {
            if (!($questions = $this->getList()->take(10)->get())) return abort(404);
            return response()->json([
                'status' => true,
                'payload' => $questions,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'payload' => 'Hệ thống đã xảy ra lỗi ! ',
            ], 404);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $skills = $this->skillModel::select('name', 'id')->get();
        return view(
            'pages.question.add',
            [
                'skills' => $skills
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dump(count($request->answers));
        // dd($request->all());
        $validator = Validator::make(
            $request->all(),
            [
                'content' => 'required',
                'type' => 'required|numeric',
                'status' => 'required|numeric',
                'rank' => 'required|numeric',
                'skill' => 'required',
                'skill.*' => 'required',
                'answers.*.content' => 'required',
                // 'answers.*.is_correct' => 'required'
            ],
            [
                'answers.*.content.required' => 'Chưa nhập trường này !',
                // 'answers.*.is_correct.required' => 'Chưa nhập trường này !',
                'content.required' => 'Chưa nhập trường này !',
                'type.required' => 'Chưa nhập trường này !',
                'type.numeric' => 'Sai định dạng !',
                'status.required' => 'Chưa nhập trường này !',
                'status.numeric' => 'Sai định dạng !',
                'rank.required' => 'Chưa nhập trường này !',
                'rank.numeric' => 'Sai định dạng !',
                'skill.required' =>  'Chưa nhập trường này !',
                'skill.*.required' =>  'Chưa nhập trường này !',
            ]
        );
        if ($validator->fails() || !isset($request->answers)) {
            if (!isset($request->answers)) {
                return redirect()->back()->withErrors($validator)->with('errorAnswerConten', 'Phải ít nhất 3 đáp án !!')->withInput($request->input());
            } else {
                if (count($request->answers) <= 2) return redirect()->back()->withErrors($validator)->with('errorAnswerConten', 'Phải ít nhất 3 đáp án !!')->withInput($request->input());
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }
        DB::beginTransaction();
        try {
            $question = $this->questionModel::create([
                'content' => $request->content,
                'type' =>  $request->type,
                'status' =>  $request->status,
                'rank' =>  $request->rank,
            ]);
            $question->skills()->syncWithoutDetaching($request->skill);
            foreach ($request->answers as  $value) {
                if ($value['content'] != null) {
                    $this->answerModel::create([
                        'content' => $value['content'],
                        'question_id' => $question->id,
                        'is_correct' => $value['is_correct'][0] ?? 0
                    ]);
                }
            }
            DB::commit();
            return Redirect::route('admin.question.index');
        } catch (\Throwable $th) {
            DB::rollBack();
            dd($th);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Questions  $questions
     * @return \Illuminate\Http\Response
     */
    public function show(Questions $questions)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Questions  $questions
     * @return \Illuminate\Http\Response
     */
    public function edit(Questions $questions, $id)
    {
        $skills = $this->skillModel::select('name', 'id')->get();
        $question = $this->questionModel::find($id)->load(['answers', 'skills']);
        // dd($question);
        return view('pages.question.edit', [
            'skills' => $skills,
            'question' => $question,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Questions  $questions
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                // 'content' => 'required|unique:questions,content,' . $id . '',
                'content' => 'required',
                'type' => 'required|numeric',
                'status' => 'required|numeric',
                'rank' => 'required|numeric',
                'skill' => 'required',
                'skill.*' => 'required',
                'answers.*.content' => 'required',
            ],
            [
                'answers.*.content.required' => 'Chưa nhập trường này !',
                'content.required' => 'Chưa nhập trường này !',
                // 'content.unique' => 'Nội dung đã tồn tại !',
                'type.required' => 'Chưa nhập trường này !',
                'type.numeric' => 'Sai định dạng !',
                'status.required' => 'Chưa nhập trường này !',
                'status.numeric' => 'Sai định dạng !',
                'rank.required' => 'Chưa nhập trường này !',
                'rank.numeric' => 'Sai định dạng !',
                'skill.required' =>  'Chưa nhập trường này !',
                'skill.*.required' =>  'Chưa nhập trường này !',
            ]
        );

        if ($validator->fails() || count($request->answers) <= 2) {
            if (count($request->answers) <= 2) {
                return redirect()->back()->withErrors($validator)->with('errorAnswerConten', 'Phải ít nhất 3 đáp án !!')->withInput($request->input());
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        // dd($request->all());
        try {
            $question = $this->questionModel::find($id);
            $question->update([
                'content' => $request->content,
                'type' =>  $request->type,
                'status' =>  $request->status,
                'rank' =>  $request->rank,
            ]);
            $question->skills()->sync($request->skill);
            foreach ($request->answers as  $value) {
                if (isset($value['answer_id'])) {
                    $this->answerModel::find($value['answer_id'])->forceDelete();
                }
            }
            foreach ($request->answers as  $value) {
                if ($value['content'] != null) {
                    $this->answerModel::create([
                        'content' => $value['content'],
                        'question_id' => $question->id,
                        'is_correct' => $value['is_correct'][0] ?? 0
                    ]);
                }
            }
            DB::commit();
            return Redirect::route('admin.question.index');
        } catch (\Throwable $th) {
            DB::rollBack();
            dd($th);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Questions  $questions
     * @return \Illuminate\Http\Response
     */
    public function destroy(Questions $questions, $id)
    {
        $this->questionModel::find($id)->delete();
        return Redirect::route('admin.question.index');
    }


    public function un_status(Request $request)
    {
        try {
            $question = $this->questionModel::find($request->id);
            $question->update([
                'status' => 0,
            ]);

            return response()->json([
                'status' => true,
                'payload' => 'Success'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'payload' => 'Không thể câp nhật trạng thái !',
            ]);
        }
    }

    public function re_status(Request $request)
    {
        try {
            $question = $this->questionModel::find($request->id);
            $question->update([
                'status' => 1,
            ]);
            return response()->json([
                'status' => true,
                'payload' => 'Success'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'payload' => 'Không thể câp nhật trạng thái !',
            ]);
        }
    }

    public function softDeleteList()
    {
        $skills = $this->skillModel::all();
        if (!($questions = $this->getList()->paginate(request('limit') ?? 5))) return abort(404);
        // dd($questions);
        return view('pages.question.list-soft-delete', [
            'questions' => $questions,
            'skills' => $skills,
        ]);
    }
    public function delete($id)
    {
        try {
            $this->questionModel::withTrashed()->where('id', $id)->forceDelete();
            return redirect()->back();
        } catch (\Throwable $th) {
            return abort(404);
        }
    }
    public function restoreDelete($id)
    {
        try {
            $this->questionModel::withTrashed()->where('id', $id)->restore();
            return redirect()->back();
        } catch (\Throwable $th) {
            return abort(404);
        }
    }
    public function save_questions(Request $request)
    {
        try {
            $ids = [];
            $exams = $this->examModel::whereId($request->exam_id)->first();
            foreach ($request->question_ids ?? [] as $question_id) {
                array_push($ids, (int)$question_id['id']);
            }
            $exams->questions()->sync($ids);
            return response()->json([
                'status' => true,
                'payload' => 'Cập nhật trạng thái thành công  !',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'payload' => 'Không thể câp nhật trạng câu hỏi  !',
            ]);
        }
    }

    public function remove_question_by_exams(Request $request)
    {
        try {
            $exams = $this->examModel::whereId($request->exam_id)->first();
            $exams->questions()->detach($request->questions_id);
            return response()->json([
                'status' => true,
                'payload' => 'Cập nhật trạng thái thành công  !',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'payload' => 'Không thể xóa câu hỏi  !',
            ]);
        }
    }
    public function exImpost(Request $request)
    {
        Excel::import(new QuestionsImport, $request->ex_file);
    }

    public function exportQe()
    {
        $point = [
            [1, 2, 3],
            [2, 5, 9]
        ];
        $data = (object) array(
            'points' => $point,
        );
        $export = new QuestionsExport([$data]);
        return Excel::download($export, 'abc.xlsx');
        // return Excel::download(new QuestionsExport, 'question.xlsx');
        // return Excel::download(new QuestionsExport, 'invoices.xlsx', true, ['X-Vapor-Base64-Encode' => 'True']);
    }
}