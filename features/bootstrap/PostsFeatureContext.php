<?php

use App\Exceptions\BehatRuntimeException;
use App\Models\Post;
use Behat\Testwork\Hook\Scope\AfterSuiteScope;

/**
 * Defines application features from the specific context.
 */
class PostsFeatureContext extends BaseFeatureContext
{
    /**
     * @var string|null
     */
    protected static ?string $postId = null;

    /**
     * @Given /^I get new post ID$/
     * @throws BehatRuntimeException
     */
    public function iGetNewPostID()
    {
        self::$postId = $this->getResponseBodyContent()->data->createPost->_id;
        return self::$postId;
    }

    /**
     * @AfterSuite
     */
    public static function deleteTestUser(AfterSuiteScope $scope)
    {
        if(self::$postId !== null){
            Post::where('_id', self::$postId)->delete();
        }
    }
}
