require 'test_helper'

class Hrm::Ensemble::EngagementControllerTest < ActionController::TestCase
  test "should get from_teacher_and_course" do
    get :from_teacher_and_course
    assert_response :success
  end

  test "should get get_teachers_on_course" do
    get :get_teachers_on_course
    assert_response :success
  end

end
