require 'test_helper'

class Lms::Project::ProjectControllerTest < ActionController::TestCase
  test "should get show" do
    get :show
    assert_response :success
  end

  test "should get get_all_for_course" do
    get :get_all_for_course
    assert_response :success
  end

  test "should get from_member_and_course" do
    get :from_member_and_course
    assert_response :success
  end

  test "should get is_member" do
    get :is_member
    assert_response :success
  end

  test "should get get_members" do
    get :get_members
    assert_response :success
  end

  test "should get add_member" do
    get :add_member
    assert_response :success
  end

  test "should get delete_member" do
    get :delete_member
    assert_response :success
  end

end
