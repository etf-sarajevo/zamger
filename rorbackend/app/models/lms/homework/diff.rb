class Lms::Homework::Diff < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'zadatakdiff'
  # alias_attribute :assignment_id, :zadatak
  # alias_attribute :diff, :diff

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'zadatakdiff'
  # ASSIGNMENT_ID =  TABLE_NAME + '.' + 'zadatak'
  # DIFF =  TABLE_NAME + '.' + 'diff'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'lms_homework_diffs'
  ASSIGNMENT_ID =  TABLE_NAME + '.' + 'assignment_id'
  DIFF =  TABLE_NAME + '.' + 'diff'

  ALL_COLUMNS = [ASSIGNMENT_ID, DIFF]


  belongs_to :assignment
  
  validates_presence_of :diff
end
