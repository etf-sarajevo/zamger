require 'test_helper'

class Lms::Forum::ForumControllerTest < ActionController::TestCase
  test "should get get_all_topics" do
    get :get_all_topics
    assert_response :success
  end

  test "should get get_topics_count" do
    get :get_topics_count
    assert_response :success
  end

  test "should get get_latest_posts" do
    get :get_latest_posts
    assert_response :success
  end

  test "should get start_new_topic" do
    get :start_new_topic
    assert_response :success
  end

end
