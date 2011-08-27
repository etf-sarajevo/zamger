require 'test_helper'

class Core::EnrollmentControllerTest < ActionController::TestCase
  test "should get get_current_for_student" do
    get :get_current_for_student
    assert_response :success
  end

  test "should get get_all_for_student" do
    get :get_all_for_student
    assert_response :success
  end

end
