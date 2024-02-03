<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\AdminUser;
use App\Question;
use App\BigQuestion;
use App\Choice;

class AdminController extends Controller
{
    public function loginIndex() {
        return view('admin.login');
    }

    public function login(Request $request) {
        $userId = $request->userId;
        $password = $request->password;
        if (!AdminUser::where('user_id', $userId)->first()) {
            return redirect('/admin/login');
        }

        $adminPassword = AdminUser::where('user_id', $userId)->first()->password;
        if (Hash::check($password, $adminPassword)) {
            return redirect('/admin');
        } else {
            return redirect('/admin/login');
        }
    }

    public function index() {
        $big_questions = BigQuestion::all();;
        $questions = Question::all();
        return view('admin.index', compact('big_questions', 'questions'));
    }

    public function editIndex($id) {
        $question = Question::find($id);
        return view('admin.edit.id', compact('question'));
    }

    public function edit(Request $request, $id) {
        // 指定されたIDの質問を検索、存在しない場合は404エラーを返す
        $question = Question::findOrFail($id);
    
        // リクエストを検証
        $validatedData = $request->validate([
            'name0' => 'required|string|max:20',
            'name1' => 'required|string|max:20',
            'name2' => 'required|string|max:20',
            'valid' => 'required|in:0,1,2', // 'valid'が0, 1, 2のいずれかであることを確認
        ]);
    
        // 質問に紐づく選択肢を取得
        $choices = $question->choices;
    
        // 選択肢の数が期待通りであるか、またはアプリケーションのロジックに合わせて調整
        if (count($choices) < 3) {
            return redirect('/admin')->withErrors(['message' => '質問に対する選択肢が不足しています。']);
        }
    
        foreach ($choices as $index => $choice) {
            // リクエストに基づいて選択肢の名前と有効性を更新
            $choice->name = $request->input('name'.$index);
            $choice->valid = ($index === intval($request->valid));
            $choice->save();
        }
    
        return redirect('/admin');
    }
    
    

    public function addIndex($id) {
        $big_question = BigQuestion::find($id);
        return view('admin.add.id', compact('big_question'));
    }

    public function add(Request $request, $id) {
        $file = $request->file;
        $fileName = $request->{'name'.$request->valid} . '.png';
        $path = public_path('img/');
        $file->move($path, $fileName);

        $question = new Question;
        $question->big_question_id = $id;
        $question->image = $fileName;
        $question->save();
        $question->choices()->saveMany([
            new Choice([
                'name' => $request->name1,
                'valid' => intval($request->valid) === 1,
            ]),
            new Choice([
                'name' => $request->name2,
                'valid' => intval($request->valid) === 2,
            ]),
            new Choice([
                'name' => $request->name3,
                'valid' => intval($request->valid) === 3,
            ]),
        ]);

        return redirect('/admin');
    }

    public function bigQuestionAddIndex() {
        return view('admin.big_question.add');
    }
    public function bigQuestionAdd(Request $request) {
        BigQuestion::create([
            'name' => $request->title
        ]);
        return redirect('/admin');
    }

    public function bigQuestionDeleteIndex($id) {
        $big_question = BigQuestion::find($id);
        return view('admin.big_question.delete', compact('big_question'));
    }

    public function bigQuestionDelete(Request $request, $big_question_id) {
        $big_question = BigQuestion::find($big_question_id);
        $questions = $big_question->questions;
        foreach($questions as $question){
            $choices = $question->choices;
            foreach($choices as $choice){
                $choice->delete();
            }
            $question->delete();
        }
        $big_question->delete();

        return redirect('/admin');  
    }
}
