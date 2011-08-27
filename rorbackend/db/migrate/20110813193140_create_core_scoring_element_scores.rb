class CreateCoreScoringElementScores < ActiveRecord::Migration
  def change
    create_table :core_scoring_element_scores do |t|
      t.integer :student_id
      t.integer :course_offering_id
      t.integer :scoring_element_id
      t.float :score

      t.timestamps
    end
  end
end
