require 'test_helper'

class Sis::AnnouncementControllerTest < ActionController::TestCase
  test "should get show" do
    get :show
    assert_response :success
  end

  test "should get get_latest_for_person" do
    get :get_latest_for_person
    assert_response :success
  end

end
