require 'test_helper'

class Lms::Moodle::MoodleItemControllerTest < ActionController::TestCase
  test "should get get_latest_for_course" do
    get :get_latest_for_course
    assert_response :success
  end

end
