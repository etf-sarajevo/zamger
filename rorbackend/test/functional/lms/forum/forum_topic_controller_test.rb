require 'test_helper'

class Lms::Forum::ForumTopicControllerTest < ActionController::TestCase
  test "should get show" do
    get :show
    assert_response :success
  end

  test "should get get_count_replies" do
    get :get_count_replies
    assert_response :success
  end

  test "should get viewed" do
    get :viewed
    assert_response :success
  end

  test "should get get_all_posts" do
    get :get_all_posts
    assert_response :success
  end

  test "should get add_reply" do
    get :add_reply
    assert_response :success
  end

end
