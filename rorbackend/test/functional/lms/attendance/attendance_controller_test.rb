require 'test_helper'

class Lms::Attendance::AttendanceControllerTest < ActionController::TestCase
  test "should get from_student_and_class" do
    get :from_student_and_class
    assert_response :success
  end

  test "should get get_presence" do
    get :get_presence
    assert_response :success
  end

  test "should get set_presence" do
    get :set_presence
    assert_response :success
  end

  test "should get update_score" do
    get :update_score
    assert_response :success
  end

end
