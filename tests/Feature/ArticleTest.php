<?php

namespace Tests\Feature;

use App\Article;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ArticleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_articles_with_pagination()
    {
        config(['model.perpage' => 2]);

        factory(Article::class)->create(['title' => "First Article"]);
        factory(Article::class)->create(['title' => "Second Article"]);
        factory(Article::class)->create(['title' => "Third Article"]);
        factory(Article::class)->create(['title' => "Fourth Article"]);
        factory(Article::class)->create(['title' => "Fifth Article"]);

        $response = $this->get('/api/articles');

        $response
            ->assertOk()
            ->assertJson([
                "data" => [
                    [
                        "id" => 5,
                        "title" => 'Fifth Article',
                    ],
                    [
                        "id" => 4,
                        "title" => "Fourth Article",
                    ]
                ],
                "meta" => [
                    "total" => 5,
                    "from" => 1,
                    "to" => 2,
                    "per_page" => 2,
                    "current_page" => 1,
                    "last_page" => 3,
                ]
            ])
            ->assertJsonStructure([
                "data" => [
                    "*" => ["id", "title", "body", "created_at", "updated_at"],
                ],
                "links" => ["first", "last", "prev", "next"],
                "meta" => [
                    "total",
                    "from",
                    "to",
                    "per_page",
                    "current_page",
                    "last_page",
                    "path",
                ]
            ]);
    }

    /** @test */
    public function it_can_show_single_article()
    {
        factory(Article::class)->create(['title' => "First Article"]);
        factory(Article::class)->create(['title' => "Second Article"]);

        $response = $this->get('/api/articles/2');

        $response
            ->assertOk()
            ->assertJson([
                "data" => ["id" => 2, "title" => "Second Article"]
            ])
            ->assertJsonStructure([
                "data" => ["id", "title", "body", "created_at", "updated_at"]
            ]);

        $response->assertJsonMissing([
            "data" => ["id" => 1, "title" => "First Article"]
        ]);
    }

    /** @test */
    public function it_can_store_a_new_article()
    {
        $response = $this->post("/api/articles", [
            "title" => "Some Article",
            "body" => "The content of the article"
        ]);

        $response->assertStatus(201)->assertJson(["created" => true]);

        $this->assertDatabaseHas('articles', [
            "title" => "Some Article",
            "body" => "The content of the article",
        ]);
    }

    /** @test */
    public function it_does_not_store_article_if_given_data_is_not_valid()
    {
        $this->withExceptionHandling();
        $response = $this->post("/api/articles", [], [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(422)->assertJson([
            "message" => "The given data was invalid.",
            "errors" => [
                "title" => ["The title field is required."]
            ]
        ]);
    }

    /** @test */
    public function it_can_update_existing_articles()
    {
        $article = factory(Article::class)->create();

        $response = $this->put("/api/articles/" . $article->id, [
            "title" => "Updated Title",
            "body" => "Updated Content",
        ]);

        $response->assertOk()->assertJson(["updated" => true]);

        $this->assertDatabaseHas('articles', [
            "title" => "Updated Title",
            "body" => "Updated Content"
        ]);

        $this->assertDatabaseMissing('articles', [
            "title" => $article->title,
            "body" => $article->body,
        ]);
    }

    /** @test */
    public function it_does_not_update_if_given_data_invalid()
    {
        $this->withExceptionHandling();

        $article = factory(Article::class)->create();

        $response = $this->put("/api/articles/" . $article->id, [], [
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(422)->assertJson([
            "message" => "The given data was invalid.",
            "errors" => [
                "title" => ["The title field is required."]
            ]
        ]);

        $this->assertDatabaseHas('articles', [
            "title" => $article->title,
            "body" => $article->body,
        ]);
    }

    /** @test */
    public function it_can_delete_existing_articles()
    {
        $article1 = factory(Article::class)->create();
        $article2 = factory(Article::class)->create();

        $response = $this->delete("/api/articles/" . $article1->id);

        $response->assertOk()->assertJson(["deleted" => true]);

        $this->assertDatabaseMissing('articles', $article1->toArray());

        $this->assertDatabaseHas('articles', $article2->toArray());
    }
}
