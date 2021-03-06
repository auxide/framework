<?php

use Mockery as m;
use Illuminate\Http\Response;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;

class FoundationCrawlerTraitIntegrationTest extends PHPUnit_Framework_TestCase
{
    use MakesHttpRequests;

    protected $baseUrl = 'https://laravel.com';

    protected function setCrawler($html)
    {
        $this->crawler = new Crawler($html);
    }

    public function testSeePageIs()
    {
        $this->currentUri = 'https://laravel.com/docs';

        $this->response = m::mock(Response::class);
        $this->response->shouldReceive('getStatusCode')
            ->once()
            ->andReturn(200);

        $this->seePageIs('/docs');
    }

    public function testSeeThroughWebCrawler()
    {
        $this->setCrawler('<p>The PHP Framework For Web Artisans</p>');
        $this->see('Web Artisans');
    }

    public function testSeeThroughResponse()
    {
        $this->crawler = null;

        $this->response = m::mock(Response::class);
        $this->response->shouldReceive('getContent')
            ->once()
            ->andReturn('<p>The PHP Framework For Web Artisans</p>');

        $this->see('Web Artisans');
    }

    public function testDontSee()
    {
        $this->setCrawler('<p>The PHP Framework For Web Artisans</p>');
        $this->dontSee('Webmasters');
    }

    public function testSeeInElement()
    {
        $this->setCrawler('<div>Laravel was created by <strong>Taylor Otwell</strong></div>');
        $this->seeInElement('strong', 'Taylor');
    }

    public function testSeeInElementSearchInAllElements()
    {
        $this->setCrawler(
            '<div>
                Laravel is a <strong>PHP framework</strong>
                created by <strong>Taylor Otwell</strong>
            </div>'
        );

        $this->seeInElement('strong', 'Taylor');
    }

    public function testSeeInElementSearchInHtmlTags()
    {
        $this->setCrawler(
            '<div id="mytable">
                <img src="image.jpg">
            </div>'
        );

        $this->seeInElement('#mytable', 'image.jpg');
    }

    public function testdontSeeInElement()
    {
        $this->setCrawler(
            '<div>Laravel was created by <strong>Taylor Otwell</strong></div>'
        );

        $this->seeInElement('strong', 'Laravel', true);
        $this->dontSeeInElement('strong', 'Laravel');
    }

    public function testSeeLink()
    {
        $this->setCrawler(
            '<a href="https://laravel.com">Laravel</a>'
        );

        $this->seeLink('Laravel');
        $this->seeLink('Laravel', 'https://laravel.com');
    }

    public function testDontSeeLink()
    {
        $this->setCrawler(
            '<a href="https://laravel.com">Laravel</a>'
        );

        $this->dontSeeLink('Symfony');
        $this->dontSeeLink('Symfony', 'https://symfonyc.com');
    }

    protected function getInputHtml()
    {
        return '<input type="text" name="framework" value="Laravel">';
    }

    public function testSeeInInput()
    {
        $this->setCrawler($this->getInputHtml());
        $this->seeInField('framework', 'Laravel');
    }

    public function testDontSeeInInput()
    {
        $this->setCrawler($this->getInputHtml());
        $this->dontSeeInField('framework', 'Rails');
    }

    protected function getInputArrayHtml()
    {
        return '<input type="text" name="framework[]" value="Laravel">';
    }

    public function testSeeInInputArray()
    {
        $this->setCrawler($this->getInputArrayHtml());
        $this->seeInField('framework[]', 'Laravel');
    }

    public function testDontSeeInInputArray()
    {
        $this->setCrawler($this->getInputArrayHtml());
        $this->dontSeeInField('framework[]', 'Rails');
    }

    protected function getTextareaHtml()
    {
        return '<textarea name="description">Laravel is awesome</textarea>';
    }

    public function testSeeInTextarea()
    {
        $this->setCrawler($this->getTextareaHtml());
        $this->seeInField('description', 'Laravel is awesome');
    }

    public function testDontSeeInTextarea()
    {
        $this->setCrawler($this->getTextareaHtml());
        $this->dontSeeInField('description', 'Rails is awesome');
    }

    protected function getSelectHtml()
    {
        return
         '<select name="availability">'
        .'    <option value="partial_time">Partial time</option>'
        .'    <option value="full_time" selected>Full time</option>'
        .'</select>';
    }

    public function testSeeOptionIsSelected()
    {
        $this->setCrawler($this->getSelectHtml());
        $this->seeIsSelected('availability', 'full_time');
    }

    public function testDontSeeOptionIsSelected()
    {
        $this->setCrawler($this->getSelectHtml());
        $this->dontSeeIsSelected('availability', 'partial_time');
    }

    protected function getRadiosHtml()
    {
        return
         '<input type="radio" name="availability" value="partial_time">'
        .'<input type="radio" name="availability" value="full_time" checked>';
    }

    public function testSeeRadioIsChecked()
    {
        $this->setCrawler($this->getRadiosHtml());
        $this->seeIsSelected('availability', 'full_time');
    }

    public function testDontSeeRadioIsChecked()
    {
        $this->setCrawler($this->getRadiosHtml());
        $this->dontSeeIsSelected('availability', 'partial_time');
    }

    protected function getCheckboxesHtml()
    {
        return
             '<input type="checkbox" name="terms" checked>'
            .'<input type="checkbox" name="list">';
    }

    public function testSeeCheckboxIsChecked()
    {
        $this->setCrawler($this->getCheckboxesHtml());
        $this->seeIsChecked('terms');
    }

    public function testDontSeeCheckboxIsChecked()
    {
        $this->setCrawler($this->getCheckboxesHtml());
        $this->dontSeeIsChecked('list');
    }

    protected function getLayoutHtml()
    {
        return
            '<header>
                <h1>Laravel</h1>
            </header>
            <section id="features">
	            <h2>The PHP Framework For Web Artisans</h2>
	            <p>Elegant applications delivered at warp speed.</p>
            </section>
            <footer>
                <a href="docs">Documentation</a>
            </footer>';
    }

    public function testWithin()
    {
        $this->setCrawler($this->getLayoutHtml());

        // Limit the search to the "header" area
        $this->within('header', function () {
            $this->see('Laravel')
                 ->dontSeeInElement('h2', 'PHP Framework');
        });

        // Make sure we are out of the within context
        $this->seeLink('Documentation');

        // Test other methods as well
        $this->within('#features', function () {
            $this->seeInElement('h2', 'PHP Framework')
                ->dontSee('Laravel')
                ->dontSeeLink('Documentation');
        });
    }

    public function testNestedWithin()
    {
        $this->setCrawler($this->getLayoutHtml());

        $this->within('#features', function () {
            $this->dontSee('Laravel')
                ->see('Web Artisans')
                ->within('h2', function () {
                    $this->see('PHP Framework')
                        ->dontSee('Elegant applications');
                });
        });
    }
}
