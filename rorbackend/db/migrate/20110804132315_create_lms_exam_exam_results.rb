class CreateLmsExamExamResults < ActiveRecord::Migration
  def change
    create_table :lms_exam_exam_results do |t|
      t.integer :student_id, :default => 0
      t.integer :exam_id, :default => 0
      t.float :result, :default => 0
      
      # t.timestamps
    end
    
    add_index :lms_exam_exam_results, [:student_id, :exam_id], :unique => true
  end
end
