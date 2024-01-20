<?php

namespace App\Tests\Utils;

use App\Utils\HtmlHelper;
use PHPUnit\Framework\TestCase;

class HtmlHelperTest extends TestCase
{

    public function testFixBuiltImageSrc()
    {
        $tests = [
            [
                'html' => '
<li>
    <p><span class="image"><img src="machine-learning-derivatives/stem-44bc9d542a92714cac84e01cbbb7fd61.png" alt="stem 44bc9d542a92714cac84e01cbbb7fd61" width="9" height="8"></span> is a constant</p>
</li>
<li>
    <p><span class="image"><img src="machine-learning-derivatives/stem-332cc365a4987aacce0ead01b8bdcc0b.png" alt="stem 332cc365a4987aacce0ead01b8bdcc0b" width="9" height="8"></span> is the variable by which we derive the functions</p>
</li>
        ',
                'expected' => '
<li>
    <p><span class="image"><img src="/articles/machine-learning-derivatives/stem-44bc9d542a92714cac84e01cbbb7fd61.png" alt="stem 44bc9d542a92714cac84e01cbbb7fd61" width="9" height="8"></span> is a constant</p>
</li>
<li>
    <p><span class="image"><img src="/articles/machine-learning-derivatives/stem-332cc365a4987aacce0ead01b8bdcc0b.png" alt="stem 332cc365a4987aacce0ead01b8bdcc0b" width="9" height="8"></span> is the variable by which we derive the functions</p>
</li>
        ',
                'message' => 'Should add prefix to image src',
            ], [
                'html' => '<img src="/lang/en.png" />',
                'expected' => '<img src="/lang/en.png" />',
                'message' => 'Shouldn`t add prefix to image src if it is not a built image.',
            ], [
                'html' => '<img src="http://test.dev/machine-learning-derivatives/stem-332cc365a4987aacce0ead01b8bdcc0b.png" />',
                'expected' => '<img src="http://test.dev/machine-learning-derivatives/stem-332cc365a4987aacce0ead01b8bdcc0b.png" />',
                'message' => 'Shouldn`t change the src if it\'s an absolute image.',
            ]
        ];

        foreach ($tests as $test) {
            $this->assertEquals($test['expected'], HtmlHelper::fixBuiltImageSrc($test['html']), $test['message']);
        }
    }
}
