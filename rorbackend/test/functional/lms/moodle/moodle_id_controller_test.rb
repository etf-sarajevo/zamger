require 'test_helper'

class Lms::Moodle::MoodleIdControllerTest < ActionController::TestCase
  test "should get get_moodle_id" do
    get :get_moodle_id
    assert_response :success
  end

end
