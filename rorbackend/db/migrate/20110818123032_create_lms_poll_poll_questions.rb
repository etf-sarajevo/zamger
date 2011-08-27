class CreateLmsPollPollQuestions < ActiveRecord::Migration
  def change
    create_table :lms_poll_poll_questions do |t|
      t.integer :poll_id, :default => 0
      t.integer :poll_question_type_id
      t.text :text

      # t.timestamps
    end
  end
end
