require 'test_helper'

class Core::RssControllerTest < ActionController::TestCase
  test "should get show" do
    get :show
    assert_response :success
  end

  test "should get from_person_id" do
    get :from_person_id
    assert_response :success
  end

  test "should get update_time_stamp" do
    get :update_time_stamp
    assert_response :success
  end

end
