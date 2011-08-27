require 'test_helper'

class Lms::Homework::AssignmentControllerTest < ActionController::TestCase
  test "should get show" do
    get :show
    assert_response :success
  end

  test "should get from_student_homework_number" do
    get :from_student_homework_number
    assert_response :success
  end

  test "should get create" do
    get :create
    assert_response :success
  end

end
