require 'test_helper'

class Core::AcademicYearControllerTest < ActionController::TestCase
  test "should get get_current" do
    get :get_current
    assert_response :success
  end

  test "should get set_as_current" do
    get :set_as_current
    assert_response :success
  end

end
