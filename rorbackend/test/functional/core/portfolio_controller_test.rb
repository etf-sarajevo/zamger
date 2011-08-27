require 'test_helper'

class Core::PortfolioControllerTest < ActionController::TestCase
  test "should get show" do
    get :show
    assert_response :success
  end

  test "should get from_course_offering" do
    get :from_course_offering
    assert_response :success
  end

  test "should get from_course_unit" do
    get :from_course_unit
    assert_response :success
  end

  test "should get get_grade" do
    get :get_grade
    assert_response :success
  end

  test "should get set_grade" do
    get :set_grade
    assert_response :success
  end

  test "should get delete_grade" do
    get :delete_grade
    assert_response :success
  end

  test "should get get_score" do
    get :get_score
    assert_response :success
  end

  test "should get set_score" do
    get :set_score
    assert_response :success
  end

  test "should get delete_score" do
    get :delete_score
    assert_response :success
  end

  test "should get get_total_score" do
    get :get_total_score
    assert_response :success
  end

  test "should get get_max_score" do
    get :get_max_score
    assert_response :success
  end

  test "should get get_latest_grades_for_student" do
    get :get_latest_grades_for_student
    assert_response :success
  end

  test "should get get_current_for_student" do
    get :get_current_for_student
    assert_response :success
  end

  test "should get get_all_for_student" do
    get :get_all_for_student
    assert_response :success
  end

end
