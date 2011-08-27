require 'test_helper'

class Lms::Attendance::GroupControllerTest < ActionController::TestCase
  test "should get show" do
    get :show
    assert_response :success
  end

  test "should get from_student_and_course" do
    get :from_student_and_course
    assert_response :success
  end

  test "should get is_member" do
    get :is_member
    assert_response :success
  end

  test "should get is_teacher" do
    get :is_teacher
    assert_response :success
  end

end
