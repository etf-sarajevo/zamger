require 'test_helper'

class Lms::Poll::PollControllerTest < ActionController::TestCase
  test "should get show" do
    get :show
    assert_response :success
  end

  test "should get get_active_for_all_courses" do
    get :get_active_for_all_courses
    assert_response :success
  end

  test "should get get_active_for_course" do
    get :get_active_for_course
    assert_response :success
  end

  test "should get is_active_for_course" do
    get :is_active_for_course
    assert_response :success
  end

end
