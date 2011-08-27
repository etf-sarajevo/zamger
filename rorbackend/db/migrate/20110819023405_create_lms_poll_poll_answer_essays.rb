class CreateLmsPollPollAnswerEssays < ActiveRecord::Migration
  def change
    create_table :lms_poll_poll_answer_essays do |t|
      t.integer :poll_result_id
      t.integer :poll_question_id
      t.text :answer

      # t.timestamps
    end
  end
end
