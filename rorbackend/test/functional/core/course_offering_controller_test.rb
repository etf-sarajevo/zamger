require 'test_helper'

class Core::CourseOfferingControllerTest < ActionController::TestCase
  test "should get show" do
    get :show
    assert_response :success
  end

  test "should get get_courses_offered" do
    get :get_courses_offered
    assert_response :success
  end

end
