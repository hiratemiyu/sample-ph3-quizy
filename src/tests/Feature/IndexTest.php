<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\BigQuestion; // BigQuestionモデルを使用する

class IndexTest extends TestCase
{   /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample()
    {
        // BigQuestionのダミーレコードを作成
        $bigQuestion = factory(BigQuestion::class)->create();

        // '/'へのGETリクエスト
        $response = $this->get('/');

        // ステータスコード200を確認
        $response->assertStatus(200);

        // レスポンスに「東京の難読地名クイズ」という文字列が含まれているか確認
        $response->assertSee('東京の難読地名クイズ');

        // レスポンスにBigQuestionのnameが含まれているか確認
        $response->assertSee($bigQuestion->name);
    }
}
