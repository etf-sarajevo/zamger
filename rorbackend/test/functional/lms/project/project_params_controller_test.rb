require 'test_helper'

class Lms::Project::ProjectParamsControllerTest < ActionController::TestCase
  test "should get from_course" do
    get :from_course
    assert_response :success
  end

end
